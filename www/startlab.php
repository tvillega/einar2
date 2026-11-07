<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup interfaces</title>
    <style>
        body {
            padding: 20px;
        }
        .form-group {
            padding: 5px 0px;
        }
        .form-group label {
            padding-right: 10px;
        }
        .submit-container {
            padding-top: 15px;
        }
        .output-container {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

    <!-- Send POST with form data to myself (executes the code at the top)  -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?laboratory=" . $_GET['laboratory']; ?>" method="post">

    <h1>Start a lab</h1>

    <h2>Currently available laboratories</h2>
    <center><p><b>Built-in laboratories</b></p></center>
    <center><p><b>Your laboratories</b></p></center>
        <div style="padding-top: 15px;">
            <button type="submit">Submit</button>
        </div>

        <div style="margin-top: 15px;">
            <a href="/setupservices.php<?php echo "?laboratory=" . $_GET['laboratory']; ?>">Previous (Edit Services)</a>
            <a href="/index.php"> | Home</a>
        </div>


</body>
</html>
