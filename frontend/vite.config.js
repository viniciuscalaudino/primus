import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const apiTarget = env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000';

  const apiProxy = {
    target: apiTarget,
    changeOrigin: true,
    configure(proxy) {
      proxy.on('proxyReq', (proxyRequest, request) => {
        const origin = request.headers.origin;

        if (origin) {
          const parsedOrigin = new URL(origin);
          proxyRequest.setHeader(
            'Origin',
            `${parsedOrigin.protocol}//${parsedOrigin.hostname}`,
          );
        }

        const referer = request.headers.referer;

        if (referer) {
          const parsedReferer = new URL(referer);
          proxyRequest.setHeader(
            'Referer',
            `${parsedReferer.protocol}//${parsedReferer.hostname}${parsedReferer.pathname}${parsedReferer.search}`,
          );
        }
      });
    },
  };

  return {
    root: '.',

    build: {
      outDir: 'dist',
      emptyOutDir: true,
      rollupOptions: {
        input: {
          home: 'index.html',
          '404': 'src/pages/404.html',
          admin: 'src/pages/admin.html',
          carrinho: 'src/pages/carrinho.html',
          checkout: 'src/pages/checkout.html',
          login: 'src/pages/login.html',
          pedido: 'src/pages/pedido.html',
          pedidos: 'src/pages/pedidos.html',
          perfil: 'src/pages/perfil.html',
          products: 'src/pages/products.html',
          product: 'src/pages/product.html',
          register: 'src/pages/register.html',
        },
      },
    },

    server: {
      proxy: {
        '/api': apiProxy,
        '/sanctum': apiProxy,
      },
    },
  };
});