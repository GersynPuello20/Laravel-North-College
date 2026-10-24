/**
 * Captura facial con reto de presencia (Alpine.js).
 *
 * La vista previa se muestra en espejo, pero los fotogramas se envían tal
 * como los ve la cámara. Nada se guarda en el navegador: los fotogramas viajan
 * al servidor, se procesan en memoria y se descartan.
 */
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const DIRECTION_TEXT = {
    left: 'Gira la cabeza hacia tu izquierda',
    right: 'Gira la cabeza hacia tu derecha',
};

export default function facialCapture(config) {
    return {
        mode: config.mode, // 'enroll' | 'attendance'
        camera: 'idle', // idle | starting | ready | denied | unavailable | error
        phase: 'idle', // idle | challenge | processing | result
        guide: { ok: false, message: 'Activa la cámara para empezar.' },
        instruction: '',
        direction: null,
        step: 0,
        result: null,
        history: [],
        stream: null,
        detectTimer: null,
        detectBusy: false,

        init() {
            window.addEventListener('pagehide', () => this.stopCamera());
        },

        get canStart() {
            return this.camera === 'ready' && this.phase !== 'challenge' && this.phase !== 'processing';
        },

        async startCamera() {
            if (!navigator.mediaDevices?.getUserMedia) {
                this.camera = 'unavailable';
                this.guide = {
                    ok: false,
                    message: window.isSecureContext
                        ? 'Este navegador no permite usar la cámara.'
                        : 'La cámara solo funciona con HTTPS o desde localhost.',
                };
                return;
            }

            this.camera = 'starting';
            this.guide = { ok: false, message: 'Solicitando permiso para usar la cámara…' };

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                    audio: false,
                });
                this.$refs.video.srcObject = this.stream;
                await this.$refs.video.play();
                this.camera = 'ready';
                this.guide = { ok: false, message: 'Coloca el rostro dentro de la guía.' };
                this.startDetectLoop();
            } catch (error) {
                this.camera = error?.name === 'NotAllowedError' ? 'denied' : 'error';
                this.guide = {
                    ok: false,
                    message:
                        this.camera === 'denied'
                            ? 'No se dio permiso para usar la cámara. Actívalo en el navegador o usa el registro manual.'
                            : 'No se pudo iniciar la cámara. Revisa que esté conectada y que otra aplicación no la esté usando.',
                };
            }
        },

        stopCamera() {
            clearInterval(this.detectTimer);
            this.stream?.getTracks().forEach((track) => track.stop());
            this.stream = null;
            if (this.camera === 'ready') this.camera = 'idle';
        },

        startDetectLoop() {
            clearInterval(this.detectTimer);
            this.detectTimer = setInterval(() => this.detectOnce(), 1500);
            this.detectOnce();
        },

        async detectOnce() {
            if (this.detectBusy || this.camera !== 'ready' || this.phase === 'challenge' || this.phase === 'processing') return;
            this.detectBusy = true;
            try {
                const data = await this.post(config.detectUrl, { image: this.frame(0.7) });
                this.guide = { ok: Boolean(data.ok), message: data.message, code: data.code };
            } catch (error) {
                this.guide = { ok: false, message: error.message, code: error.code };
            } finally {
                this.detectBusy = false;
            }
        },

        frame(quality = 0.85) {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', quality);
        },

        async burst(count, gap) {
            const frames = [];
            for (let i = 0; i < count; i++) {
                frames.push(this.frame());
                if (i < count - 1) await sleep(gap);
            }
            return frames;
        },

        async run() {
            if (!this.canStart) return;
            this.result = null;

            let challenge = { id: null, direction: null };
            try {
                const data = await this.post(config.challengeUrl, {});
                if (!data.ok) {
                    this.showResult(data);
                    return;
                }
                challenge = data.challenge;
            } catch (error) {
                this.showResult({ ok: false, code: error.code, message: error.message });
                return;
            }

            this.phase = 'challenge';
            this.direction = challenge.direction;
            const frames = [];

            this.step = 1;
            this.instruction = 'Mira de frente a la cámara';
            await sleep(1200);
            frames.push(...(await this.burst(2, 300)));

            this.step = 2;
            this.instruction = DIRECTION_TEXT[challenge.direction] ?? 'Gira la cabeza hacia un lado';
            await sleep(900);
            frames.push(...(await this.burst(4, 350)));

            this.step = 3;
            this.instruction = 'Vuelve a mirar de frente';
            await sleep(900);
            frames.push(...(await this.burst(2, 300)));

            this.phase = 'processing';
            this.instruction = this.mode === 'enroll' ? 'Generando la representación biométrica…' : 'Verificando identidad…';

            try {
                const data = await this.post(config.submitUrl, { challenge_id: challenge.id, frames });
                this.showResult(data);
            } catch (error) {
                this.showResult({ ok: false, code: error.code, message: error.message });
            }
        },

        showResult(data) {
            this.phase = 'result';
            this.step = 0;
            this.result = data;
            this.instruction = '';

            if (this.mode === 'attendance') {
                this.history.unshift({ ...data, at: new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }) });
                this.history = this.history.slice(0, 10);
            }

            if (this.mode === 'enroll' && data.ok) {
                this.stopCamera();
                setTimeout(() => window.location.reload(), 1800);
            }
        },

        reset() {
            this.phase = 'idle';
            this.result = null;
        },

        async post(url, body) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });

            let data = {};
            try {
                data = await response.json();
            } catch {
                // Respuesta sin JSON (p. ej. error del servidor web).
            }

            if (response.status === 419) throw Object.assign(new Error('La página caducó. Recárgala para continuar.'), { code: 'csrf' });
            if (response.status === 429) throw Object.assign(new Error('Demasiados intentos seguidos. Espera un momento.'), { code: 'throttled' });
            if (response.status === 403) throw Object.assign(new Error('No tienes permiso para esta acción.'), { code: 'forbidden' });
            if (response.status === 422 && data.errors) {
                throw Object.assign(new Error('La captura enviada no es válida. Inténtalo de nuevo.'), { code: 'invalid_capture' });
            }
            if (!response.ok && !data.message) {
                throw Object.assign(new Error('No se pudo completar la operación.'), { code: 'http_' + response.status });
            }
            if (!response.ok && data.ok === undefined) {
                throw Object.assign(new Error(data.message), { code: data.code });
            }

            return data;
        },
    };
}
