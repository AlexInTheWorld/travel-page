<!DOCTYPE html>
<head>
    <script src="validate.js" type="text/javascript"></script>
    <script src="show_cities.js" type="text/javascript" defer></script>
    <link href="/public/styles.css" type="stylesheet">
</head>
<html>
    <body>
        <ul></ul>
        <?php
            if (!isset($_SESSION["loggedin"])) {
                echo "<li><a href='/login'>Log In</a></li><li><a href='/register'>Register</a></li>";
            } else {
                echo "<li><a href='/logout'></a></li>";
            }  

            if($city) {
                echo "<p>You would like to go to " .  $city . ". Is that correct?";
            }
        ?>

        <form action="#" method="GET">
            <label for="city">Which city would you like to visit?</label>
            <input type="text" name="city" id="city" placeholder="E.g. Amsterdam">
            <div id="cities">
                <input type="submit" value="Submit">
                <ul id="suggestions"></ul>
            </div>
        </form>
    </body>


</html>



