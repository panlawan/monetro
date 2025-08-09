#!/bin/bash

# =============================================================================
# 🚀 MCR INSTALL SCRIPT - ระบบบันทึกรายรับรายจ่าย และการลงทุน
# =============================================================================
# Model-Controller-Request Installation Script
# สร้าง Models, Controllers, Requests, Migrations ครบชุด
# =============================================================================

echo "🎯 MCR Install Script - Personal Finance & Investment System"
echo "=============================================================="
echo "📅 $(date)"
echo ""

# ตรวจสอบว่าอยู่ใน Laravel project หรือไม่
if [ ! -f "artisan" ]; then
    echo "❌ Error: ไม่พบไฟล์ artisan - กรุณาเรียกใช้ script ใน Laravel project root"
    exit 1
fi

echo "🔍 ตรวจสอบ Laravel project... ✅"
echo ""

# =============================================================================
# 📋 STEP 1: สร้าง MIGRATIONS
# =============================================================================

echo "📋 STEP 1: สร้าง Database Migrations"
echo "=================================="

# 1. Users table (เพิ่ม columns)
echo "👤 สร้าง Users migration (เพิ่ม columns)..."
# php artisan make:migration add_additional_columns_to_users_table --table=users

# 2. Categories
echo "🏷️ สร้าง Categories migration..."
php artisan make:migration create_categories_table

# 3. Accounts
echo "🏪 สร้าง Accounts migration..."
php artisan make:migration create_accounts_table

# 4. Transactions
echo "💰 สร้าง Transactions migration..."
php artisan make:migration create_transactions_table

# 5. Transaction Tags
echo "🏷️ สร้าง Transaction Tags migration..."
php artisan make:migration create_transaction_tags_table

# 6. Transaction Tag Pivot
echo "🔗 สร้าง Transaction Tag Pivot migration..."
php artisan make:migration create_transaction_tag_pivot_table

# 7. Transfers
echo "🔄 สร้าง Transfers migration..."
php artisan make:migration create_transfers_table

# 8. Investment Types
echo "💎 สร้าง Investment Types migration..."
php artisan make:migration create_investment_types_table

# 9. Investment Assets
echo "🏢 สร้าง Investment Assets migration..."
php artisan make:migration create_investment_assets_table

# 10. Investment Transactions
echo "📊 สร้าง Investment Transactions migration..."
php artisan make:migration create_investment_transactions_table

# 11. Dividends
echo "💰 สร้าง Dividends migration..."
php artisan make:migration create_dividends_table

# 12. Budget Plans
echo "📈 สร้าง Budget Plans migration..."
php artisan make:migration create_budget_plans_table

# 13. Budget Categories
echo "🎯 สร้าง Budget Categories migration..."
php artisan make:migration create_budget_categories_table

# 14. Financial Goals
echo "🎚️ สร้าง Financial Goals migration..."
php artisan make:migration create_financial_goals_table

# 15. Monthly Summaries
echo "📊 สร้าง Monthly Summaries migration..."
php artisan make:migration create_monthly_summaries_table

# 16. Recurring Transactions
echo "📱 สร้าง Recurring Transactions migration..."
php artisan make:migration create_recurring_transactions_table

# 17. Transaction Attachments
echo "📄 สร้าง Transaction Attachments migration..."
php artisan make:migration create_transaction_attachments_table

# 18. User Preferences
echo "⚙️ สร้าง User Preferences migration..."
php artisan make:migration create_user_preferences_table

php artisan make:migration add_expense_fields_to_users_table
 
echo ""
echo "✅ Migrations สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 🏗️ STEP 2: สร้าง MODELS
# =============================================================================

echo "🏗️ STEP 2: สร้าง Eloquent Models"
echo "==============================="

# Core Models
echo "👤 สร้าง User model (already exists - will be updated)..."

echo "🏷️ สร้าง Category model..."
php artisan make:model Category

echo "🏪 สร้าง Account model..."
php artisan make:model Account

echo "💰 สร้าง Transaction model..."
php artisan make:model Transaction

echo "🏷️ สร้าง TransactionTag model..."
php artisan make:model TransactionTag

echo "🔄 สร้าง Transfer model..."
php artisan make:model Transfer

# Investment Models
echo "💎 สร้าง InvestmentType model..."
php artisan make:model InvestmentType

echo "🏢 สร้าง InvestmentAsset model..."
php artisan make:model InvestmentAsset

echo "📊 สร้าง InvestmentTransaction model..."
php artisan make:model InvestmentTransaction

echo "💰 สร้าง Dividend model..."
php artisan make:model Dividend

# Budget & Goals Models
echo "📈 สร้าง BudgetPlan model..."
php artisan make:model BudgetPlan

echo "🎯 สร้าง BudgetCategory model..."
php artisan make:model BudgetCategory

echo "🎚️ สร้าง FinancialGoal model..."
php artisan make:model FinancialGoal

# Reporting Models
echo "📊 สร้าง MonthlySummary model..."
php artisan make:model MonthlySummary

# Utility Models
echo "📱 สร้าง RecurringTransaction model..."
php artisan make:model RecurringTransaction

echo "📄 สร้าง TransactionAttachment model..."
php artisan make:model TransactionAttachment

echo "⚙️ สร้าง UserPreference model..."
php artisan make:model UserPreference

echo ""
echo "✅ Models สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 🎮 STEP 3: สร้าง CONTROLLERS
# =============================================================================

echo "🎮 STEP 3: สร้าง Controllers"
echo "=========================="

# Dashboard Controller
echo "🏠 สร้าง Dashboard controller..."
php artisan make:controller DashboardController

# Core Controllers
echo "🏷️ สร้าง Category controller..."
php artisan make:controller CategoryController --resource

echo "🏪 สร้าง Account controller..."
php artisan make:controller AccountController --resource

echo "💰 สร้าง Transaction controller..."
php artisan make:controller TransactionController --resource

echo "🏷️ สร้าง TransactionTag controller..."
php artisan make:controller TransactionTagController --resource

echo "🔄 สร้าง Transfer controller..."
php artisan make:controller TransferController --resource

# Investment Controllers
echo "💎 สร้าง InvestmentType controller..."
php artisan make:controller InvestmentTypeController --resource

echo "🏢 สร้าง InvestmentAsset controller..."
php artisan make:controller InvestmentAssetController --resource

echo "📊 สร้าง InvestmentTransaction controller..."
php artisan make:controller InvestmentTransactionController --resource

echo "💰 สร้าง Dividend controller..."
php artisan make:controller DividendController --resource

# Budget & Goals Controllers
echo "📈 สร้าง BudgetPlan controller..."
php artisan make:controller BudgetPlanController --resource

echo "🎯 สร้าง BudgetCategory controller..."
php artisan make:controller BudgetCategoryController --resource

echo "🎚️ สร้าง FinancialGoal controller..."
php artisan make:controller FinancialGoalController --resource

# Reporting Controllers
echo "📊 สร้าง Report controller..."
php artisan make:controller ReportController

echo "📈 สร้าง Analytics controller..."
php artisan make:controller AnalyticsController

# Utility Controllers
echo "📱 สร้าง RecurringTransaction controller..."
php artisan make:controller RecurringTransactionController --resource

echo "📄 สร้าง TransactionAttachment controller..."
php artisan make:controller TransactionAttachmentController --resource

echo "⚙️ สร้าง UserPreference controller..."
php artisan make:controller UserPreferenceController --resource

# API Controllers (สำหรับ Ajax/Charts)
echo "🔌 สร้าง API controllers..."
php artisan make:controller Api/ChartController
php artisan make:controller Api/StatsController
php artisan make:controller Api/SearchController

echo ""
echo "✅ Controllers สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 📝 STEP 4: สร้าง FORM REQUESTS
# =============================================================================

echo "📝 STEP 4: สร้าง Form Request Classes"
echo "==================================="

# Core Requests
echo "🏷️ สร้าง Category requests..."
php artisan make:request StoreCategoryRequest
php artisan make:request UpdateCategoryRequest

echo "🏪 สร้าง Account requests..."
php artisan make:request StoreAccountRequest
php artisan make:request UpdateAccountRequest

echo "💰 สร้าง Transaction requests..."
php artisan make:request StoreTransactionRequest
php artisan make:request UpdateTransactionRequest

echo "🔄 สร้าง Transfer requests..."
php artisan make:request StoreTransferRequest
php artisan make:request UpdateTransferRequest

# Investment Requests
echo "💎 สร้าง InvestmentType requests..."
php artisan make:request StoreInvestmentTypeRequest
php artisan make:request UpdateInvestmentTypeRequest

echo "🏢 สร้าง InvestmentAsset requests..."
php artisan make:request StoreInvestmentAssetRequest
php artisan make:request UpdateInvestmentAssetRequest

echo "📊 สร้าง InvestmentTransaction requests..."
php artisan make:request StoreInvestmentTransactionRequest
php artisan make:request UpdateInvestmentTransactionRequest

echo "💰 สร้าง Dividend requests..."
php artisan make:request StoreDividendRequest
php artisan make:request UpdateDividendRequest

# Budget & Goals Requests
echo "📈 สร้าง BudgetPlan requests..."
php artisan make:request StoreBudgetPlanRequest
php artisan make:request UpdateBudgetPlanRequest

echo "🎚️ สร้าง FinancialGoal requests..."
php artisan make:request StoreFinancialGoalRequest
php artisan make:request UpdateFinancialGoalRequest

# Utility Requests
echo "📱 สร้าง RecurringTransaction requests..."
php artisan make:request StoreRecurringTransactionRequest
php artisan make:request UpdateRecurringTransactionRequest

echo "⚙️ สร้าง UserPreference requests..."
php artisan make:request UpdateUserPreferenceRequest

echo ""
echo "✅ Form Requests สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 🛡️ STEP 5: สร้าง POLICIES (Authorization)
# =============================================================================

echo "🛡️ STEP 5: สร้าง Authorization Policies"
echo "======================================"

echo "🏷️ สร้าง Category policy..."
php artisan make:policy CategoryPolicy --model=Category

echo "🏪 สร้าง Account policy..."
php artisan make:policy AccountPolicy --model=Account

echo "💰 สร้าง Transaction policy..."
php artisan make:policy TransactionPolicy --model=Transaction

echo "🏢 สร้าง InvestmentAsset policy..."
php artisan make:policy InvestmentAssetPolicy --model=InvestmentAsset

echo "📊 สร้าง InvestmentTransaction policy..."
php artisan make:policy InvestmentTransactionPolicy --model=InvestmentTransaction

echo "📈 สร้าง BudgetPlan policy..."
php artisan make:policy BudgetPlanPolicy --model=BudgetPlan

echo "🎚️ สร้าง FinancialGoal policy..."
php artisan make:policy FinancialGoalPolicy --model=FinancialGoal

echo ""
echo "✅ Policies สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 🏭 STEP 6: สร้าง FACTORIES & SEEDERS
# =============================================================================

echo "🏭 STEP 6: สร้าง Factories & Seeders"
echo "=================================="

# Factories
echo "🏭 สร้าง Factories..."
php artisan make:factory CategoryFactory --model=Category
php artisan make:factory AccountFactory --model=Account
php artisan make:factory TransactionFactory --model=Transaction
php artisan make:factory InvestmentTypeFactory --model=InvestmentType
php artisan make:factory InvestmentAssetFactory --model=InvestmentAsset
php artisan make:factory InvestmentTransactionFactory --model=InvestmentTransaction

# Seeders
echo "🌱 สร้าง Seeders..."
php artisan make:seeder UserSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder AccountSeeder
php artisan make:seeder InvestmentTypeSeeder
php artisan make:seeder TransactionSeeder
php artisan make:seeder InvestmentAssetSeeder
php artisan make:seeder UserPreferenceSeeder

echo ""
echo "✅ Factories & Seeders สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 📧 STEP 7: สร้าง NOTIFICATIONS & MAIL
# =============================================================================

echo "📧 STEP 7: สร้าง Notifications & Mail"
echo "=================================="

echo "📧 สร้าง Notification classes..."
php artisan make:notification BudgetExceededNotification
php artisan make:notification GoalAchievedNotification
php artisan make:notification MonthlyReportNotification
php artisan make:notification LowBalanceNotification

echo "📧 สร้าง Mailable classes..."
php artisan make:mail MonthlyFinancialReport
php artisan make:mail InvestmentSummaryReport

echo ""
echo "✅ Notifications & Mail สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# ⚡ STEP 8: สร้าง JOBS & COMMANDS
# =============================================================================

echo "⚡ STEP 8: สร้าง Jobs & Console Commands"
echo "====================================="

echo "⚡ สร้าง Job classes..."
php artisan make:job ProcessRecurringTransactions
php artisan make:job UpdateInvestmentPrices
php artisan make:job GenerateMonthlySummary
php artisan make:job SendMonthlyReports

echo "🖥️ สร้าง Console Commands..."
php artisan make:command ProcessRecurringTransactionsCommand
php artisan make:command UpdateInvestmentPricesCommand
php artisan make:command GenerateMonthlyReportsCommand
php artisan make:command CalculatePortfolioCommand

echo ""
echo "✅ Jobs & Commands สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 🧪 STEP 9: สร้าง TESTS
# =============================================================================

echo "🧪 STEP 9: สร้าง Test Classes"
echo "============================"

echo "🧪 สร้าง Feature Tests..."
php artisan make:test TransactionTest
php artisan make:test AccountTest
php artisan make:test InvestmentTest
php artisan make:test ReportTest

echo "🧪 สร้าง Unit Tests..."
php artisan make:test TransactionCalculationTest --unit
php artisan make:test InvestmentCalculationTest --unit
php artisan make:test BudgetCalculationTest --unit

echo ""
echo "✅ Tests สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 🎨 STEP 10: สร้าง ADDITIONAL RESOURCES
# =============================================================================

echo "🎨 STEP 10: สร้าง Additional Resources"
echo "======================================"

echo "🎨 สร้าง Resource Classes..."
php artisan make:resource TransactionResource
php artisan make:resource TransactionCollection
php artisan make:resource InvestmentAssetResource
php artisan make:resource AccountResource

echo "🎨 สร้าง Component Classes..."
php artisan make:component Charts/LineChart
php artisan make:component Charts/PieChart
php artisan make:component Charts/BarChart
php artisan make:component Forms/TransactionForm
php artisan make:component Forms/InvestmentForm

echo "🔧 สร้าง Service Classes..."
mkdir -p app/Services
touch app/Services/TransactionService.php
touch app/Services/InvestmentService.php
touch app/Services/ReportService.php
touch app/Services/BudgetService.php
touch app/Services/CalculationService.php

echo "🔧 สร้าง Repository Classes..."
mkdir -p app/Repositories
touch app/Repositories/TransactionRepository.php
touch app/Repositories/InvestmentRepository.php
touch app/Repositories/ReportRepository.php

echo ""
echo "✅ Additional Resources สร้างเสร็จสิ้น!"
echo ""

# =============================================================================
# 📦 FINAL SUMMARY
# =============================================================================

echo ""
echo "🎉 MCR INSTALL COMPLETE!"
echo "========================"
echo ""
echo "📋 สรุปไฟล์ที่สร้าง:"
echo "==================="
echo "🗃️  Migrations:           18 ไฟล์"
echo "🏗️  Models:               16 ไฟล์"
echo "🎮 Controllers:           20 ไฟล์"
echo "📝 Form Requests:         22 ไฟล์"
echo "🛡️  Policies:             7 ไฟล์"
echo "🏭 Factories:             6 ไฟล์"
echo "🌱 Seeders:               7 ไฟล์"
echo "📧 Notifications:         4 ไฟล์"
echo "📧 Mails:                 2 ไฟล์"
echo "⚡ Jobs:                  4 ไฟล์"
echo "🖥️  Commands:             4 ไฟล์"
echo "🧪 Tests:                 7 ไฟล์"
echo "🎨 Resources:             4 ไฟล์"
echo "🎨 Components:            5 ไฟล์"
echo "🔧 Services:              5 ไฟล์"
echo "🔧 Repositories:          3 ไฟล์"
echo ""
echo "📁 รวมทั้งสิ้น:            ~140 ไฟล์"
echo ""
echo "🚀 ขั้นตอนต่อไป:"
echo "==============="
echo "1. ✏️  เขียน migration schemas"
echo "2. 🔗 ตั้งค่า model relationships"
echo "3. 📝 เขียน validation rules ใน form requests"
echo "4. 🎮 implement controller logic"
echo "5. 🎨 สร้าง blade views"
echo "6. 🛣️  ตั้งค่า routes"
echo "7. 🧪 เขียน tests"
echo ""
echo "🎯 Ready to code! Happy development! 🚀"
echo ""