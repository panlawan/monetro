<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hello Monetro</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="p-5">

    <nav class="navbar navbar-expand-lg bg-primary navbar-dark mb-4 shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-coins"></i> Monetro
            </a>
        </div>
    </nav>

    <div class="container">

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-start-success monetro-card-stat card-hover">
                    <div class="card-body">
                        <div class="stat-label">Income</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-value">$1,200</div>
                            <div class="stat-icon icon-success"><i class="fas fa-arrow-up fa-money-bounce"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-start-danger monetro-card-stat card-hover">
                    <div class="card-body">
                        <div class="stat-label">Expenses</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-value">$850</div>
                            <div class="stat-icon icon-danger"><i class="fas fa-arrow-down fa-money-bounce"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-start-warning monetro-card-stat card-hover">
                    <div class="card-body">
                        <div class="stat-label">Investments</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-value">$3,200</div>
                            <div class="stat-icon icon-warning"><i class="fas fa-chart-line fa-pulse-slow"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="text-gray-700 mb-3">Market Overview</h4>

        <div class="market-item mb-2">
            <div class="d-flex justify-content-between">
                <div class="text-gray-800">Bitcoin</div>
                <div class="market-price text-success">$29,000</div>
            </div>
            <div class="market-change icon-success"><i class="fas fa-arrow-up"></i> 2.3%</div>
        </div>

        <div class="market-item mb-2">
            <div class="d-flex justify-content-between">
                <div class="text-gray-800">Ethereum</div>
                <div class="market-price text-danger">$1,800</div>
            </div>
            <div class="market-change icon-danger"><i class="fas fa-arrow-down"></i> -1.1%</div>
        </div>




    </div>

</body>
</html>
