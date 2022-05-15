<!DOCTYPE html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="validate.js" type="text/javascript"></script>
    <script src="show_cities.js" type="text/javascript" defer></script>
    <link href="styles.css" rel="stylesheet"> 
</head>

<html>
    <body>
        <main>

            <?php
                $not_logged_el = "<ul id='not_logged_el' class='user-status-el'><li><a href='/login'>Log In</a></li><li><a href='/register'>Register</a></li></ul>";
                $logged_el = "<ul id='logged_el' class='user-status-el'><li><a href='/logout'>Logout</a></li></ul>";

                $el_to_show = isset($_SESSION["logged_in"]) ? $_SESSION["logged_in"] ? $logged_el : $not_logged_el : $not_logged_el;

                echo $el_to_show;
            ?>
            
            <form action="#" method="POST">
                <!-- <input type="hidden" name="geonameId" value="" id="geonameId"> -->
                <label for="city">Which city would you like to visit?</label>
                <div id="form-scaffold">
                    <div id="cities" style="position: relative;">
                        <div id="inputs">
                            <input type="text" name="city" id="city" placeholder="E.g. Amsterdam">
                        </div>
                        <ul id="suggestions"></ul>
                    </div>
                    <button type="submit">Submit</button>
                </div>
     
            </form>

            <section id="results">
                <div id="info-msg"></div>
                <div id="results-view"></div>                
            </section>
            
        </main>
    </body>
</html>