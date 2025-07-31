#!/bin/bash
echo "🔧 กำลังแก้ไขสำหรับ Tailwind v4..."

# 1. ลบ packages เก่า
npm uninstall tailwindcss @tailwindcss/forms autoprefixer postcss

# 2. ติดตั้ง Tailwind v4
npm install -D @tailwindcss/vite@next

# 3. แก้ไข vite.config.js สำหรับ v4
cat > vite.config.js << 'VITE_CONFIG'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
VITE_CONFIG

# 4. แก้ไข resources/css/app.css สำหรับ v4
cat > resources/css/app.css << 'APP_CSS'
@import "tailwindcss";

@source "../views/**/*.blade.php";
@source "../**/*.js";

@theme {
  --font-sans: Figtree, ui-sans-serif, system-ui, sans-serif;
  --font-mono: ui-monospace, SFMono-Regular, Consolas, monospace;
}

@layer components {
  .btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background-color: theme(colors.gray.800);
    border: 1px solid transparent;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.15s ease-in-out;
    
    &:hover {
      background-color: theme(colors.gray.700);
    }
    
    &:focus {
      outline: 2px solid theme(colors.indigo.500);
      outline-offset: 2px;
    }
  }
}
APP_CSS

# 5. Update package.json สำหรับ v4
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
        "@tailwindcss/vite": "next",
        "alpinejs": "^3.14.1",
        "axios": "^1.8.2",
        "laravel-vite-plugin": "^2.0.0",
        "vite": "^7.0.4"
    }
}
PACKAGE_JSON

echo "✅ แก้ไข Tailwind v4 เสร็จสิ้น!"
