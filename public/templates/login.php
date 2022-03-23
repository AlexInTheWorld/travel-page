<!DOCTYPE html>
<head>
    <link href="/public/login/styles.css" rel="stylesheet">
</head>
<html>
    <body>
        <form action="/login" method="POST">

        <div class="container">
            <label for="uname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="uname" minlength="4" maxlength="20" required>
            <?php 
                if ($uname_error) {
                    echo "<p>" . $uname_error . "</p>";
                }
            ?>
            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="psw" minlength="8" maxlength="20" required>
            <?php 
                if ($psw_error) {
                    echo "<p>" . $psw_error . "</p>";
                }
            ?>
            <button type="submit">Login</button>
        </div>
        <!--
        <div class="container" style="background-color:#f1f1f1">
            <button type="button" class="cancelbtn">Cancel</button>
            <span class="psw">Forgot <a href="#">password?</a></span>
        </div>
        -->
        </form>
    </body>
</html>

