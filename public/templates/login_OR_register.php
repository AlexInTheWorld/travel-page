<!DOCTYPE html>
<head>
    <link href="form_styles.css" rel="stylesheet">
    <?php if($type === "register"): ?>
    <script src="register.js" type="text/javascript" defer></script>
    <?php endif; ?>
</head>
<html>
    <body>
        <h1><?=$type?> FORM</h1>
        <form action="/<?=$type?>" method="POST">
            <div class="container">
                <?php 
                    if ($error) {
                        echo "<p style='color:darkred;font-weight:bold;'>" . $error . "</p>";
                    }
                ?>
                <label for="uname"><b>Username</b></label>
                <input type="text" id="uname" placeholder="<?=$type==='login' ? 'Enter Username' : 'Enter a unique username'?>" name="uname" minlength="2" maxlength="20" required>
                <label for="psw"><b>Password</b></label>
                <input type="password" id="psw" placeholder="<?=$type==='login' ? 'Enter Password' : 'Enter a valid password'?>" name="psw" minlength="8" required>
                <button type="submit"><?=$type?></button>
            </div>
        </form>
    </body>
</html>

