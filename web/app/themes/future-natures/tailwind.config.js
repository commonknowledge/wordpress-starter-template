/** @typedef { import('tailwindcss/defaultConfig') } DefaultConfig */
/** @typedef { import('tailwindcss/defaultTheme') } DefaultTheme */
/** @typedef { DefaultConfig & { theme: { extend: DefaultTheme } } } TailwindConfig */

/** @type {TailwindConfig} */
module.exports = {
  content: [
    "./block-templates/**/*.html",
    "./block-template-parts/**/*.html",
    "**/*.php",
  ],
  theme: {
    extend: {
      screens: {
        xs: "480px",
      },
      fontFamily: {
        body: ["var(--wp--preset--typography--body-font)"],
        display: ["var(--wp--preset--typography--heading-font)"],
      },
      colors: {
        "dark-green": "var(--wp--preset--color--dark-green)",
        green: "var(--wp--preset--color--green)",
        "mid-green": "var(--wp--preset--color--mid-green)",
        "light-green": "var(--wp--preset--color--light-green)",
        purple: "var(--wp--preset--color--purple)",
        pink: "var(--wp--preset--color--pink)",
        black: "var(--wp--preset--color--black)",
        "light-grey": "var(--wp--preset--color--light-grey)",
      },
      width: {
        "4xl": "56rem",
      },
    },
  },
  plugins: [],
};
