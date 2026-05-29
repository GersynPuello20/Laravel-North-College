/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],

  theme: {
    extend: {
      colors: {
        primary: '#0F2C59',
        secondary: '#D72638',
        light: '#F4F7FC',
      },
    },
  },

  plugins: [],
}