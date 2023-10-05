import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    outDir: 'dist/widget',
    lib: {
      entry: './src/utils/currencyChartD3.js',
      name: 'chartsD3Lib',
      fileName: 'chartsD3Lib',
      formats: ['umd']
    },
  },
})