import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devHost = env.VITE_DEV_HOST || 'muster.test';
    const herdCertificatesPath = path.join(
        process.env.HOME || '',
        'Library',
        'Application Support',
        'Herd',
        'config',
        'valet',
        'Certificates',
    );
    const certFile = path.join(herdCertificatesPath, `${devHost}.crt`);
    const keyFile = path.join(herdCertificatesPath, `${devHost}.key`);
    const hasHerdCertificate = fs.existsSync(certFile) && fs.existsSync(keyFile);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            cors: true,
            ...(hasHerdCertificate
                ? {
                      https: {
                          cert: fs.readFileSync(certFile),
                          key: fs.readFileSync(keyFile),
                      },
                  }
                : {}),
            hmr: {
                host: devHost,
                protocol: hasHerdCertificate ? 'wss' : 'ws',
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
