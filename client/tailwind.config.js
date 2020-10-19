module.exports = {
  prefix: 'tw-',
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
    defaultLineHeights: true,
    standardFontWeights: true
  },
  purge: [
    './src/**/*.vue'
  ],
  theme: {
    extend: {}
  },
  variants: {},
  plugins: []
}
