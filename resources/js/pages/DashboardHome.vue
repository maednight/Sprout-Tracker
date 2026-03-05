    <template>
    <div class="min-h-screen w-full sprout-shell">
        <div class="w-full max-w-[430px] md:max-w-[980px] mx-auto px-4 py-5">

        <!-- Top bar -->
        <div class="flex items-center justify-between mb-3">
            <button class="text-sm font-medium text-green-600 flex items-center gap-1">
            Daily <span class="text-xs">▼</span>
            </button>

            <div class="flex items-center gap-3">
            <button class="text-green-600 text-xl" @click="prevMonth">‹</button>
            <div class="font-semibold">This Month</div>
            <button class="text-green-600 text-xl" @click="nextMonth">›</button>
            </div>

            <div class="w-10"></div>
        </div>

        <!-- Calendar card -->
        <div class="bg-white/70 rounded-2xl p-4 shadow-sm border border-black/5">
            <div class="text-center font-semibold text-green-600 mb-2">
            {{ monthName }}
            </div>

            <!-- Week headers -->
            <div class="grid grid-cols-7 text-xs text-gray-400 mb-2">
            <div v-for="d in weekDays" :key="d" class="text-center">{{ d }}</div>
            </div>

            <!-- Days grid -->
            <div class="grid grid-cols-7 gap-2">
            <div v-for="cell in calendarCells" :key="cell.key"
                class="rounded-xl p-2 min-h-[52px] bg-black/[0.03] flex flex-col items-center justify-start">
                <div class="text-sm font-medium text-gray-500" :class="cell.isCurrentMonth ? 'opacity-100' : 'opacity-30'">
                {{ cell.day }}
                </div>

                <!-- amounts -->
                <div v-if="cell.totals && cell.isCurrentMonth" class="mt-1 flex flex-col gap-0.5 text-[11px] leading-tight">
                <div v-if="cell.totals.income > 0" class="text-green-600">₱{{ fmt(cell.totals.income) }}</div>
                <div v-if="cell.totals.expense > 0" class="text-rose-400">₱{{ fmt(cell.totals.expense) }}</div>
                <div v-if="cell.totals.savings > 0" class="text-amber-500">₱{{ fmt(cell.totals.savings) }}</div>
                </div>
            </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-3 gap-3 mt-4 text-center">
            <div>
                <div class="text-xs text-gray-400">Income</div>
                <div class="font-semibold text-green-600">₱{{ fmt(summary.income) }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Expense</div>
                <div class="font-semibold text-rose-400">₱{{ fmt(summary.expense) }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Balance</div>
                <div class="font-semibold text-gray-700">₱{{ fmt(summary.balance) }}</div>
            </div>
            </div>
        </div>

        <!-- Recent -->
        <div class="mt-4 space-y-3">
            <div class="bg-white/70 rounded-2xl border border-black/5 overflow-hidden">
            <div class="px-4 py-3 text-sm font-semibold">Recent Transactions</div>
            <div v-if="recent.length === 0" class="px-4 pb-4 text-sm text-gray-500">
                No transactions yet. Tap + to add one.
            </div>

            <div v-for="t in recent" :key="t.id" class="px-4 py-3 border-t border-black/5 flex items-center justify-between">
                <div class="flex flex-col">
                <div class="text-sm font-medium">
                    {{ t.category?.name || 'Uncategorized' }}
                    <span class="text-xs text-gray-400">• {{ typeLabel(t.type) }}</span>
                </div>
                <div class="text-xs text-gray-400">
                    {{ formatDateTime(t.occurred_at) }}
                    <span v-if="t.description" class="ml-2">• {{ t.description }}</span>
                </div>
                </div>

                <div class="text-sm font-semibold"
                    :class="t.type === 'income' ? 'text-green-600' : (t.type === 'expense' ? 'text-rose-400' : 'text-amber-500')">
                {{ t.type === 'expense' ? '-' : '+' }}₱{{ fmt(t.amount) }}
                </div>
            </div>
            </div>
        </div>

        <!-- Floating button -->
        <button @click="openModal"
                class="fixed bottom-24 right-6 md:right-10 w-14 h-14 rounded-full bg-green-500 text-white text-3xl flex items-center justify-center shadow-lg">
            +
        </button>

        <!-- Modal -->
        <div v-if="show" class="fixed inset-0 bg-black/30 flex items-end md:items-center justify-center p-4">
            <div class="w-full max-w-[520px] bg-white rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Add Transaction</div>
                <button class="text-gray-500" @click="show=false">✕</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                <label class="text-xs text-gray-500">Type</label>
                <select v-model="form.type" class="w-full h-11 rounded-xl border border-black/10 px-3">
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="savings">Savings</option>
                </select>
                </div>

                <div>
                <label class="text-xs text-gray-500">Amount</label>
                <input v-model="form.amount" type="number" step="0.01" class="w-full h-11 rounded-xl border border-black/10 px-3" />
                </div>

                <div>
                <label class="text-xs text-gray-500">Date</label>
                <input v-model="form.date" type="date" class="w-full h-11 rounded-xl border border-black/10 px-3" />
                </div>

                <div>
                <label class="text-xs text-gray-500">Time</label>
                <input v-model="form.time" type="time" class="w-full h-11 rounded-xl border border-black/10 px-3" />
                </div>

                <div>
                <label class="text-xs text-gray-500">Category (type to create)</label>
                <input v-model="form.category" type="text" class="w-full h-11 rounded-xl border border-black/10 px-3" placeholder="e.g. Salary" />
                </div>

                <div>
                <label class="text-xs text-gray-500">Account (type to create)</label>
                <input v-model="form.account" type="text" class="w-full h-11 rounded-xl border border-black/10 px-3" placeholder="e.g. Cash" />
                </div>

                <div class="md:col-span-2">
                <label class="text-xs text-gray-500">Description</label>
                <input v-model="form.description" type="text" class="w-full h-11 rounded-xl border border-black/10 px-3" placeholder="Optional notes..." />
                </div>
            </div>

            <div v-if="error" class="text-sm text-red-600 mt-3">{{ error }}</div>

            <div class="mt-4 flex gap-2 justify-end">
                <button class="h-11 px-4 rounded-xl border border-black/10" @click="show=false">Cancel</button>
                <button class="h-11 px-4 rounded-xl bg-green-500 text-white font-semibold" @click="save">Save</button>
            </div>
            </div>
        </div>

        </div>
    </div>
    </template>

    <script setup>
    import { computed, onMounted, reactive, ref } from 'vue'

    const weekDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']

    const month = ref(new Date()) // current month base
    const summary = reactive({ income: 0, expense: 0, savings: 0, balance: 0 })
    const calendarMap = ref({})
    const recent = ref([])

    const show = ref(false)
    const error = ref('')
    const form = reactive({
    type: 'expense',
    amount: '',
    date: '',
    time: '',
    category: '',
    account: '',
    description: '',
    })

    const monthName = computed(() => month.value.toLocaleString(undefined, { month: 'long' }))

    function fmt(n) {
    const num = typeof n === 'string' ? Number(n) : Number(n || 0)
    return num.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })
    }

    function typeLabel(t) {
    if (t === 'income') return 'IN'
    if (t === 'expense') return 'OUT'
    return 'SAV'
    }

    function formatDateTime(iso) {
    const d = new Date(iso)
    return d.toLocaleString(undefined, { weekday: 'short', month: 'short', day: '2-digit' }) + ' • ' +
        d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
    }

    function yyyymm(d) {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    return `${y}-${m}`
    }

    const calendarCells = computed(() => {
    const base = new Date(month.value.getFullYear(), month.value.getMonth(), 1)
    const startDay = (base.getDay() + 6) % 7 // convert Sun(0) → 6, Mon(1) → 0
    const daysInMonth = new Date(month.value.getFullYear(), month.value.getMonth() + 1, 0).getDate()

    const cells = []
    const totalCells = 42

    for (let i = 0; i < totalCells; i++) {
        const dayNum = i - startDay + 1
        const date = new Date(month.value.getFullYear(), month.value.getMonth(), dayNum)
        const isCurrentMonth = dayNum >= 1 && dayNum <= daysInMonth
        const key = date.toISOString().slice(0, 10)
        const totals = calendarMap.value[key] || null

        cells.push({
        key,
        day: date.getDate(),
        isCurrentMonth,
        totals,
        })
    }
    return cells
    })

    async function fetchDashboard() {
    error.value = ''
    const res = await fetch(`/api/dashboard?month=${yyyymm(month.value)}`, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
    })

    if (!res.ok) {
        error.value = 'Failed to load dashboard data.'
        return
    }

    const data = await res.json()
    summary.income = data.summary.income
    summary.expense = data.summary.expense
    summary.savings = data.summary.savings
    summary.balance = data.summary.balance
    calendarMap.value = data.calendar || {}
    recent.value = data.recent || []
    }

    function prevMonth() {
    month.value = new Date(month.value.getFullYear(), month.value.getMonth() - 1, 1)
    fetchDashboard()
    }
    function nextMonth() {
    month.value = new Date(month.value.getFullYear(), month.value.getMonth() + 1, 1)
    fetchDashboard()
    }

    function openModal() {
    const now = new Date()
    form.date = now.toISOString().slice(0, 10)
    form.time = now.toTimeString().slice(0, 5)
    form.amount = ''
    form.category = ''
    form.account = ''
    form.description = ''
    form.type = 'expense'
    error.value = ''
    show.value = true
    }

    async function save() {
    error.value = ''
    const payload = { ...form }

    const res = await fetch('/api/transactions', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
    })

    if (!res.ok) {
        const data = await res.json().catch(() => null)
        error.value = data?.message || 'Failed to save transaction.'
        return
    }

    show.value = false
    await fetchDashboard()
    }

    onMounted(fetchDashboard)
    </script>