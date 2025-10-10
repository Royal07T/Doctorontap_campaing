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
        primary: {
          DEFAULT: '#7B3DE9',
          dark: '#5a2ba8',
        },
      },
    },
  },
  plugins: [],
}

