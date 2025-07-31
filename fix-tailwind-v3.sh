#!/bin/bash
echo "🔧 กำลังแก้ไขสำหรับ Tailwind v3..."

# 1. ลบ packages ที่ขัดแย้ง
npm uninstall @tailwindcss/vite

# 2. ติดตั้ง dependencies ที่ถูกต้องสำหรับ v3
npm install -D tailwindcss@^3.4.0 postcss autoprefixer @tailwindcss/forms

# 3. สร้าง tailwind.config.js
cat > tailwind.config.js << 'TAILWIND_CONFIG'
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...require('tailwindcss/defaultTheme').fontFamily.sans],
      },
    },
  },
  plugins: [require('@tailwindcss/forms')],
}
TAILWIND_CONFIG

# 4. สร้าง postcss.config.js
cat > postcss.config.js << 'POSTCSS_CONFIG'
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
POSTCSS_CONFIG

# 5. แก้ไข vite.config.js สำหรับ v3
cat > vite.config.js << 'VITE_CONFIG'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
VITE_CONFIG

# 6. แก้ไข resources/css/app.css สำหรับ v3
cat > resources/css/app.css << 'APP_CSS'
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
    .btn {
        @apply inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150;
    }
}
APP_CSS

# 7. Update package.json
cat > package.json << 'PACKAGE_JSON'
{
    "$schema": "https://json.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.9",
        "alpinejs": "^3.14.1",
        "autoprefixer": "^10.4.20",
        "axios": "^1.8.2",
        "laravel-vite-plugin": "^2.0.0",
        "postcss": "^8.4.47",
        "tailwindcss": "^3.4.0",
        "vite": "^7.0.4"
    }
}
PACKAGE_JSON

echo "✅ แก้ไข Tailwind v3 เสร็จสิ้น!"
