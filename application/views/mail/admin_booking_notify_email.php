<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        * {

            box-sizing: border-box;
            padding: 0;
            margin: 0;
            font-family: "Montserrat", sans-serif;
            font-size: 13px;

        }
    </style>
</head>

<body>
    <!--styling the container-->
    <div id="container"
        style="width: 100%; height: auto; padding: 10px 10px 10px 10px; background-color: white; position: relative; border-radius:5px; ">

        <!--Message -->
        <div id="amount"
            style="position: relative; width: 100%; margin:auto; text-align: left; font-size: 13px; margin-top: 20px; margin-bottom: 20px;">
            Hello,

            <br><br>

            There is a new booking from <?=$sender_name?>.

            <div id="amount" style="position: relative; font-size: 13px; margin-top: 50px; margin-bottom: 20px;">
                <a href="https://sharemybag.uk/admin" target="_blank"
                    style="text-decoration: none; padding: 10px;background-color: #3d3dce; color: white; border-radius: 5px;">Login
                    your admin page</a>
            </div>
        </div>

        <!-- Footer -->
        <div id="fooooo"
            style="background-color:rgb(240, 240, 240); width: 100%; text-align: center; color: #494949; padding: 20px; ">
            <!--Logo-->
            <div id="icon" style="position: center; margin-bottom: 10px">
                <img src="cid:logo" style="width:100px;">
            </div>
            <span>&copy;</span>2023 ShareMyBag. All rights reserved.
        </div>

    </div>

</body>

</html>