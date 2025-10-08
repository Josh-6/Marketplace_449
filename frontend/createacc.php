<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Whatcha Eating?</title>
    <link rel="stylesheet" href="css/create-signin.css?v=1">
</head>

<body>
    <div class="form-background">
        <div class="form-popup">
            <h1>Create an account</h1>
            <form class="form-container" id="createAccountForm" method="post" action="../backend/register.php"> 
                
                <label for="user"><b>User Name</b></label>
                <input type="text" id="user" placeholder="Enter User Name" name="user" required />

                <label for="psw"><b>Password</b></label>
                <input type="password" id="psw" placeholder="Enter Password" name="psw" required />

                <label for="psw-repeat"><b>Repeat Password</b></label>
                <input type="password" id="psw-repeat" placeholder="Repeat Password" name="psw-repeat" required />

                <div id="reveal-password">
                    <input type="checkbox" id="showPassword"> 
                    <label for="showPassword">Show Passwords</label>
                </div>

                <button type="submit" class="btn submit">Create Account</button>
                <button type="button" class="btn cancel" onclick="location.href='index.php'">Cancel</button>
            </form>
        </div>
    </div>
    <script src="js/create-signin.js"></script>
    <!--<script type="module" src="js/createacc.js" defer></script>-->
</body>

</html>

<!--onclick="location.href='index.php'" is fine for now, backend will later handle routing