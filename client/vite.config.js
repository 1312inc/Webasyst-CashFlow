import { fileURLToPath, URL } from 'node:url';
import { ViteEjsPlugin } from 'vite-plugin-ejs';

import { defineConfig, loadEnv } from 'vite';
import vue2 from '@vitejs/plugin-vue2';

const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args));

// https://vitejs.dev/config/
export default defineConfig(async ({mode}) => {
  // eslint-disable-next-line no-undef
  const env = loadEnv(mode, process.cwd(), '')

  let currencies,
  accounts,
  categories,
  settings

  if (mode === 'development') {
    currencies = await (await fetch(`${env.VITE_APP_DEV_PROXY}/api.php/cash.system.getCurrencies?access_token=${env.VITE_APP_API_TOKEN}`)).text();
    accounts = await (await fetch(`${env.VITE_APP_DEV_PROXY}/api.php/cash.account.getList?access_token=${env.VITE_APP_API_TOKEN}`)).text();
    categories = await (await fetch(`${env.VITE_APP_DEV_PROXY}/api.php/cash.category.getList?access_token=${env.VITE_APP_API_TOKEN}`)).text();
    settings = await (await fetch(`${env.VITE_APP_DEV_PROXY}/api.php/cash.system.getSettings?access_token=${env.VITE_APP_API_TOKEN}`)).text();
  }

  return {
    plugins: [
      vue2(),
      ViteEjsPlugin({
        // eslint-disable-next-line no-undef
        mode: process.env.VITE_BUILD_MODE,
        token: env.VITE_APP_API_TOKEN,
        currencies: currencies,
        categories: accounts,
        accounts: categories,
        settings: settings
      })
    ],
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: '@import "./src/assets/styles/mixins/_breakpoint";'
        }
      }
    },
    resolve: {
      extensions: ['.js', '.json', '.vue'],
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    },
    build: {
      // eslint-disable-next-line no-undef
      outDir: `dist/${process.env.VITE_BUILD_MODE}`,
      rollupOptions: {
        external: (id) => /xlsx|canvg|pdfmake/.test(id)
      }
    },
    server: {
      proxy: {
        '/api.php': env.VITE_APP_DEV_PROXY
      }
    }
  };
});
