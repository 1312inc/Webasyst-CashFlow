import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    lib: {
      entry: './src/utils/currencyChartD3.js',
      name: 'MyLib',
      fileName: 'my-lib',
    },
    // rollupOptions: {
    //   input: './src/utils/currencyChartD3.js',
    //   output: {
    //     name: 'bundle',
    //     // file: './dist/bundle.js',
    //     format: 'cjs',
    //     dir: 'dist',
    //     inlineDynamicImports: true
    //   }
    // },
  },
})