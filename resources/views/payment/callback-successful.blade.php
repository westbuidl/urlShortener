<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Market Place</title>
    
    <link rel="stylesheet" href="https://agroease.trade/sellerdashboard/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://agroease.trade/sellerdashboard/assets/css/success.css">
   

</head>

<body>

    <section class=" hero-banner">
        <div class="container">
            <div class="bread-crumb">
                <a href="/marketplace/marketplace.html">
                    <img src="https://agroease.trade/sellerdashboard/assets/img/home-1 1.svg" alt="" />
                </a>
                <a href="#">
                <img src="https://agroease.trade/sellerdashboard/assets/img/Vector.svg" alt="" />
                </a>
                <a href="#" class="cart"> Shopping Cart </a>
                <a href="#">
                <img src="https://agroease.trade/sellerdashboard/assets/img/Vector.svg" alt="" />
                </a>
                <a href="#" class="category-active"> Checkout </a>
            </div>
        </div>
    </section>
    <section class="main-body">
        <div class="loading">
            <img src="https://agroease.trade/sellerdashboard/assets/img/Success State icon.png" alt="loading" class="img">
        </div>
        <div class="transaction">
            <h3>Transaction {{ $data['status'] }}</h3>
        </div>
        <div class="order">
            <div class="your-order">
                <p>Your Order with Order No: {{ $data['id'] }} was placed successfully </p>
            </div>
            <div class="reference-no">
                <p>Reference No: {{ $data['reference'] }}</p>
            </div>
        </div>
        <div class="total-transaction">
            <p>Total Transactions</p>
            <h5>₦{{ $data['amount'] }}</h5>
        </div>
        <button class="button">Continue Shopping</button>
        

    </section>


    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        function navigateToPage(page) {
            window.location.href = page;
        }
    </script>
</body>

</html>