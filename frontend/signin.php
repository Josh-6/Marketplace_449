<?php
// signup.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Whatcha Eating?</title>
    <link rel="stylesheet" href="css/create-signin.css?v=1">
</head>

<body>
    <div class="form-background">
        <div class="form-popup">
            <h1>Sign in or create an account</h1>
            <form class="form-container" id="siginForm"> 
                

                <label for="user"><b>User Name</b></label>
                <input type="text" id="user" placeholder="Enter User Name" name="user_name" required />

                <label for="psw"><b>Password</b></label>
                <input type="password" id="psw" placeholder="Enter Password" name="psw" required />
                
                <div id = "reveal-password">
                    <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
                </div>
                <!--btn submit is causing a small issue that it has no where to currently send data to so need to click-->
                <!--which causes page to refresh instead of sending to index, will fix itself when backend implemented-->
                <button type="submit" class="btn submit" onclick="location.href='index.php'">Sign in</button>
                <button type="button" class="btn cancel" onclick="location.href='index.php'">Close</button>

                <div class="form">
                    <p>Don't have an account? <a href="createacc.php">Start Here.</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="js/create-signin.js"></script>
    <!--<script type = "module" src="js/signin.js" defer></script>-->
</body>

</html>

<!--onclick="location.href='index.php'" is fine for now, backend will later handle routing