// resources/js/dashboard.js
import Chart from 'chart.js/auto'

export default function dashboard() {
  return {
    // ----- state -----
    from: '',
    to: '',
    months: 12,

    summary: {
      total_income: 0,
      total_expense: 0,
      net_income: 0,
      transaction_count: 0,
    },

    monthly: { 
      months: [], 
      income: [], 
      expense: [], 
      net: [] 
    },

    // chart instances
    chartIncExp: null,
    chartNet: null,
    chartsInitialized: false,
    isInitializing: false, // ป้องกัน double initialization

    // ----- lifecycle -----
    async init() {
      // ป้องกัน double initialization
      if (this.isInitializing) {
        console.log('Already initializing, skipping...')
        return
      }
      
      this.isInitializing = true
      console.log('Dashboard initializing...')
      console.log('Chart.js available:', typeof Chart !== 'undefined')
      
      try {
        await this.refresh()
        await this.loadMonthly()
        
        // รอให้ DOM พร้อมก่อนสร้างกราฟ
        await this.waitForDOM()
        
        // ตรวจสอบว่าสร้างกราฟแล้วหรือยัง
        if (!this.chartsInitialized) {
          this.drawCharts()
        }
        
        console.log('Dashboard initialized successfully')
      } catch (e) {
        console.error('Dashboard init error:', e)
      } finally {
        this.isInitializing = false
      }
    },

    destroy() {
      console.log('Destroying dashboard charts...')
      this.destroyCharts()
      this.isInitializing = false
    },

    destroyCharts() {
      if (this.chartIncExp) {
        try {
          this.chartIncExp.destroy()
          console.log('Income chart destroyed')
        } catch (e) {
          console.warn('Error destroying income chart:', e)
        }
        this.chartIncExp = null
      }
      if (this.chartNet) {
        try {
          this.chartNet.destroy()
          console.log('Net chart destroyed')
        } catch (e) {
          console.warn('Error destroying net chart:', e)
        }
        this.chartNet = null
      }
      this.chartsInitialized = false
    },

    // รอให้ DOM พร้อม
    async waitForDOM() {
      return new Promise((resolve) => {
        const checkDOM = () => {
          const canvas1 = this.$refs.incExpCanvas
          const canvas2 = this.$refs.netCanvas
          
          if (canvas1 && canvas2 && 
              canvas1.offsetParent !== null && 
              canvas2.offsetParent !== null &&
              canvas1.clientWidth > 0 && 
              canvas2.clientWidth > 0) {
            console.log('DOM ready for charts')
            resolve()
          } else {
            console.log('Waiting for DOM...')
            setTimeout(checkDOM, 50)
          }
        }
        checkDOM()
      })
    },

    // ----- helpers -----
    fmt(n) {
      const v = Number(n ?? 0)
      return new Intl.NumberFormat('en-US', { 
        maximumFractionDigits: 2,
        minimumFractionDigits: 0 
      }).format(v)
    },

    async applyRange() {
      console.log('Applying range...')
      try {
        await this.refresh()
        await this.loadMonthly()
        await this.waitForDOM()
        this.drawCharts()
        console.log('Range applied successfully')
      } catch (e) {
        console.error('Apply range error:', e)
      }
    },

    // ----- API calls -----
    async refresh() {
      try {
        const url = new URL('/api/dashboard/summary', window.location.origin)
        if (this.from) url.searchParams.set('from', this.from)
        if (this.to) url.searchParams.set('to', this.to)

        console.log('Fetching summary from:', url.toString())
        
        const res = await fetch(url, { 
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          } 
        })
        
        if (!res.ok) {
          throw new Error(`Summary API failed: ${res.status} - ${res.statusText}`)
        }
        
        const data = await res.json()
        console.log('Summary data received:', data)
        this.summary = data
      } catch (e) {
        console.error('Refresh summary error:', e)
        // Fallback data
        this.summary = {
          total_income: 0,
          total_expense: 0,
          net_income: 0,
          transaction_count: 0,
        }
      }
    },

    async loadMonthly() {
      try {
        const params = new URLSearchParams()
        if (this.from && this.to) {
          params.set('from', this.from)
          params.set('to', this.to)
        } else {
          params.set('months', this.months)
        }
        
        const url = `/api/dashboard/monthly?${params.toString()}`
        console.log('Fetching monthly from:', url)
        
        const res = await fetch(url, {
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        
        if (!res.ok) {
          throw new Error(`Monthly API failed: ${res.status} - ${res.statusText}`)
        }
        
        const data = await res.json()
        console.log('Monthly data received:', data)
        this.monthly = data
      } catch (e) {
        console.error('Load monthly error:', e)
        // Fallback data
        this.monthly = {
          months: [],
          income: [],
          expense: [],
          net: []
        }
      }
    },

    // ----- charts -----
    drawCharts() {
      console.log('Drawing charts with data:', this.monthly)
      console.log('Charts already initialized:', this.chartsInitialized)
      
      // ถ้าสร้างแล้ว ให้ทำลายก่อน
      if (this.chartsInitialized) {
        console.log('Charts already exist, destroying first...')
        this.destroyCharts()
      }
      
      // ตรวจสอบว่ามี Chart.js หรือไม่
      if (typeof Chart === 'undefined') {
        console.error('Chart.js is not available!')
        return
      }
      
      // ตรวจสอบว่ามีข้อมูลหรือไม่
      if (!this.monthly.months || this.monthly.months.length === 0) {
        console.warn('No chart data available - months array is empty')
        return
      }

      // ตรวจสอบ DOM elements
      const canvas1 = this.$refs.incExpCanvas
      const canvas2 = this.$refs.netCanvas
      
      if (!canvas1 || !canvas2) {
        console.error('Canvas elements not found')
        return
      }

      if (canvas1.offsetParent === null || canvas2.offsetParent === null) {
        console.error('Canvas elements not visible')
        return
      }

      // รอ tick ถัดไปก่อนสร้างกราฟใหม่
      setTimeout(() => {
        this.createCharts()
      }, 50)
    },

    createCharts() {
      try {
        console.log('Creating charts...')
        console.log('Chart.js available in createCharts:', typeof Chart !== 'undefined')
        
        // ตรวจสอบว่าไม่ได้สร้างไปแล้ว
        if (this.chartsInitialized) {
          console.log('Charts already created, skipping...')
          return
        }
        
        // ตรวจสอบ Chart.js อีกครั้ง
        if (typeof Chart === 'undefined') {
          console.error('Chart.js not available in createCharts')
          return
        }
        
        // ตรวจสอบอีกครั้งว่า DOM ยังพร้อม
        const canvas1 = this.$refs.incExpCanvas
        const canvas2 = this.$refs.netCanvas
        
        if (!canvas1 || !canvas2) {
          console.error('Canvas elements disappeared')
          return
        }

        // ตรวจสอบว่า canvas ไม่ได้ถูกใช้แล้ว
        const ctx1 = canvas1.getContext('2d')
        const ctx2 = canvas2.getContext('2d')
        
        // Clear canvas ก่อนสร้างใหม่
        ctx1.clearRect(0, 0, canvas1.width, canvas1.height)
        ctx2.clearRect(0, 0, canvas2.width, canvas2.height)

        // light/dark friendly colors
        const isDark = document.documentElement.classList.contains('dark') ||
                      document.documentElement.dataset.theme === 'dark'

        const axisColor = isDark ? 'rgba(229,231,235,0.7)' : 'rgba(55,65,81,0.8)'
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)'

        // Bar: Income vs Expense
        console.log('Creating income/expense chart')
        this.chartIncExp = new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: this.monthly.months,
            datasets: [
              { 
                label: 'Income', 
                data: this.monthly.income, 
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderColor: 'rgba(34,197,94,1)',
                borderWidth: 1
              },
              { 
                label: 'Expense', 
                data: this.monthly.expense, 
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderColor: 'rgba(239,68,68,1)',
                borderWidth: 1
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false, // ปิด animation เพื่อป้องกัน DOM issues
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: { 
                labels: { color: axisColor },
                position: 'top'
              },
              tooltip: { 
                mode: 'index', 
                intersect: false,
                backgroundColor: isDark ? 'rgba(17,24,39,0.9)' : 'rgba(255,255,255,0.9)',
                titleColor: axisColor,
                bodyColor: axisColor,
                borderColor: gridColor,
                borderWidth: 1
              },
            },
            scales: {
              x: { 
                ticks: { color: axisColor }, 
                grid: { color: gridColor },
                title: {
                  display: true,
                  text: 'Month',
                  color: axisColor
                }
              },
              y: { 
                ticks: { color: axisColor }, 
                grid: { color: gridColor },
                title: {
                  display: true,
                  text: 'Amount',
                  color: axisColor
                }
              },
            },
          },
        })
        console.log('Income/expense chart created successfully')

        // Line: Net Income
        console.log('Creating net income chart')
        this.chartNet = new Chart(ctx2, {
          type: 'line',
          data: {
            labels: this.monthly.months,
            datasets: [{
              label: 'Net Income',
              data: this.monthly.net,
              tension: 0.3,
              fill: false,
              borderColor: 'rgba(59,130,246,1)',
              backgroundColor: 'rgba(59,130,246,0.1)',
              borderWidth: 2,
              pointBackgroundColor: 'rgba(59,130,246,1)',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointRadius: 4,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false, // ปิด animation เพื่อป้องกัน DOM issues
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: { 
                labels: { color: axisColor },
                position: 'top'
              },
              tooltip: { 
                mode: 'index', 
                intersect: false,
                backgroundColor: isDark ? 'rgba(17,24,39,0.9)' : 'rgba(255,255,255,0.9)',
                titleColor: axisColor,
                bodyColor: axisColor,
                borderColor: gridColor,
                borderWidth: 1
              },
            },
            scales: {
              x: { 
                ticks: { color: axisColor }, 
                grid: { color: gridColor },
                title: {
                  display: true,
                  text: 'Month',
                  color: axisColor
                }
              },
              y: { 
                ticks: { color: axisColor }, 
                grid: { color: gridColor },
                title: {
                  display: true,
                  text: 'Net Amount',
                  color: axisColor
                }
              },
            },
          },
        })
        console.log('Net income chart created successfully')

        this.chartsInitialized = true
        console.log('All charts created successfully')
        
      } catch (error) {
        console.error('Error creating charts:', error)
        this.chartsInitialized = false
      }
    },
  }
}