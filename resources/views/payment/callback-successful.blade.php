<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Market Place</title>
    <!--<link rel="stylesheet" href=css/success.css>-->
    <link rel="stylesheet" href="{{assets('assets/css/success.css') }}">

    <link rel="stylesheet" href="{{assets('assets/css/bootstrap.min.css') }}">

</head>

<body>

    <section class=" hero-banner">
        <div class="container">
            <div class="bread-crumb">
                <a href="/marketplace/marketplace.html">
                    <img src="./img/home-1 1.svg" alt="" />
                </a>
                <a href="#">
                    <img src="./img/Vector.svg" alt="" />
                </a>
                <a href="#" class="cart"> Shopping Cart </a>
                <a href="#">
                    <img src="./img/Vector.svg" alt="" />
                </a>
                <a href="#" class="category-active"> Checkout </a>
            </div>
        </div>
    </section>
    <section class="main-body">
        <div class="loading">
            <img src="./img/Success State icon.png" alt="loading" class="img">
        </div>
        <div class="transaction">
            <h3>Transaction Successful</h3>
        </div>
        <div class="order">
            <div class="your-order">
                <p>Your Order with Order No: 32275864 was placed successfully </p>
            </div>
            <div class="reference-no">
                <p>Reference No: 242325625226</p>
            </div>
        </div>
        <div class="total-transaction">
            <p>Total Transactions</p>
            <h5>₦25,000.00</h5>
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