import './bootstrap'
import Alpine from 'alpinejs'
import dashboard from './dashboard'   // << นำเข้าจากข้อ 1

window.Alpine = Alpine
Alpine.data('dashboard', dashboard)   // << ลงทะเบียนชื่อ 'dashboard'
Alpine.start()
