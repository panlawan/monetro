import './bootstrap'
import Alpine from 'alpinejs'
import dashboard from './dashboard'

// ลบ Chart import ออก เพราะจะ import ใน dashboard.js เอง

window.Alpine = Alpine
Alpine.data('dashboard', dashboard)
Alpine.start()

// Debug logging
console.log('App.js loaded successfully')
console.log('Alpine.js version:', Alpine.version)