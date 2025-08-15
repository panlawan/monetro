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
      total_transfers: 0,
      transaction_count: 0,
      transfer_count: 0,
    },

    series: { labels: [], income: [], expense: [], net: [] },

    // chart instances
    chartIncExp: null,
    chartNet: null,

    // ----- lifecycle -----
    async init() {
      try {
        await this.refresh()
        await this.loadMonthly()
        this.$nextTick(() => this.drawCharts())
      } catch (e) {
        console.error(e)
      }
    },

    destroy() {
      if (this.chartIncExp) this.chartIncExp.destroy()
      if (this.chartNet) this.chartNet.destroy()
      this.chartIncExp = null
      this.chartNet = null
    },

    // ----- helpers -----
    fmt(n) {
      const v = Number(n ?? 0)
      return new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(v)
    },

    async applyRange() {
      await this.refresh()
      await this.loadMonthly()
      this.drawCharts()
    },

    // ----- API calls -----
    async refresh() {
      const url = new URL('/api/dashboard/summary', window.location.origin)
      if (this.from) url.searchParams.set('from', this.from)
      if (this.to) url.searchParams.set('to', this.to)

      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      if (!res.ok) throw new Error(`summary ${res.status}`)
      this.summary = await res.json()
    },

    async loadMonthly() {
      const p = new URLSearchParams();
      if (this.from && this.to) {
        p.set('from', this.from);
        p.set('to', this.to);
      } else {
        p.set('months', this.months);
      }
      const res = await fetch(`/api/dashboard/monthly?${p.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error(`monthly ${res.status}`);
      this.monthly = await res.json();
    },

    // async applyRange() {
    //   await this.refresh();       // อัปเดตการ์ด
    //   await this.loadMonthly();   // โหลดกราฟตามช่วงเดียวกัน
    //   this.drawCharts();
    // },

    // ----- charts -----
    drawCharts() {
      // light/dark friendly colors
      const isDark =
        document.documentElement.classList.contains('dark') ||
        document.documentElement.dataset.theme === 'dark'

      const axisColor = isDark ? 'rgba(229,231,235,0.7)' : 'rgba(55,65,81,0.8)'
      const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)'

      // Bar: Income vs Expense
      const ctx1 = this.$refs.incExpCanvas?.getContext('2d')
      if (ctx1) {
        if (this.chartIncExp) this.chartIncExp.destroy()
        this.chartIncExp = new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: this.series.labels,
            datasets: [
              { label: 'Income', data: this.series.income, backgroundColor: 'rgba(34,197,94,0.7)' },
              { label: 'Expense', data: this.series.expense, backgroundColor: 'rgba(239,68,68,0.7)' },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            resizeDelay: 200,
            plugins: {
              legend: { labels: { color: axisColor } },
              tooltip: { mode: 'index', intersect: false },
            },
            scales: {
              x: { ticks: { color: axisColor }, grid: { color: gridColor } },
              y: { ticks: { color: axisColor }, grid: { color: gridColor } },
            },
          },
        })
      }

      // Line: Net
      const ctx2 = this.$refs.netCanvas?.getContext('2d')
      if (ctx2) {
        if (this.chartNet) this.chartNet.destroy()
        this.chartNet = new Chart(ctx2, {
          type: 'line',
          data: {
            labels: this.series.labels,
            datasets: [{
              label: 'Net',
              data: this.series.net,
              tension: 0.3,
              fill: false,
              borderWidth: 2,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            resizeDelay: 200,
            plugins: {
              legend: { labels: { color: axisColor } },
              tooltip: { mode: 'index', intersect: false },
            },
            scales: {
              x: { ticks: { color: axisColor }, grid: { color: gridColor } },
              y: { ticks: { color: axisColor }, grid: { color: gridColor } },
            },
          },
        })
      }
    },
  }
}
