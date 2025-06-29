const {
  spacing
} = require('tailwindcss/defaultTheme');

const colors = require('tailwindcss/colors');

const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
  theme: {
    extend: {
      screens: {
        'sm': '640px',
        // => @media (min-width: 640px) { ... }
        'md': '768px',
        // => @media (min-width: 768px) { ... }
        'lg': '1024px',
        // => @media (min-width: 1024px) { ... }
        'xl': '1280px',
        // => @media (min-width: 1280px) { ... }
        '2xl': '1536px' // => @media (min-width: 1536px) { ... }
      },
      fontFamily: {
        sans: ['Roboto', '微軟正黑體', "Microsoft JhengHei", 'sans-serif', '-apple-system', 'system-ui', 'BlinkMacSystemFont', "Segoe UI"]
      },
      fontSize:{
        h1: ['3rem', { lineHeight: '1.3' }],
        h2: ['2.2rem', { lineHeight: '1.2' }],
        h3: ['2rem', { lineHeight: '1.2' }],
        h4: ['1.5rem', { lineHeight: '1.2' }],
        h5: ['1.3rem', { lineHeight: '1.4' }],
        h6: ['1rem', { lineHeight: '1.5' }],
      },
      colors: {
        primary: "var(--main-color, #00519c)",
        secondary: "var(--secondary-color, #44b1f6)",
        background: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        green: colors.emerald,
        yellow: colors.amber,
        purple: colors.violet,
        main: 'var(--main-color, #00519c)',
        black: 'rgb(49, 49, 57)'
      },
      textColor: {
        orange: colors.orange,
        red: { ...colors.red,
          "DEFAULT": colors.red['500']
        },
        primary: 'var(--main-color, #00519c)',
        secondary: "var(--secondary-color, #44b1f6)"
      },
      backgroundColor: {
        header:{
          "DEFAULT": "rgb(49, 49, 57)",
        },
        primary: "var(--main-color, #00519c)",
        secondary: "var(--secondary-color, #44b1f6)",
        black:"rgb(49, 49, 57)",
        container: "#ffffff",
      },
      borderColor: {
        primary: 'var(--main-color, #00519c)',
        secondary: "var(--secondary-color, #44b1f6)",
        container: {
          lighter: colors.neutral['100'],
          "DEFAULT": '#e7e7e7',
          darker: '#b6b6b6'
        }
      },
      minHeight: {
        a11y: spacing["11"],
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      maxHeight: {
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      container: {
        center: true,
        padding: spacing["6"]
      }
    }
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  // Examples for excluding patterns from purge
  content: [
    // this theme's phtml and layout XML files
    '../../**/*.phtml',
    '../../*/layout/*.xml',
    '../../*/page_layout/override/base/*.xml',
    // parent theme in Vendor (if this is a child-theme)
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml',
    '../../../../../../../vendor/hyva-themes/magento2-hyva-widgets/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-magezon-builder/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-magezon-ninja-menus/**/*.phtml',
    // app/code phtml files (if need tailwind classes from app/code modules)
    //'../../../../../../../app/code/**/*.phtml',
  ]
});
