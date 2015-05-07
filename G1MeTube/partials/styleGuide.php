<html>
<head>
    <title>Style Guide</title>
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/text.css" />
    <link rel="stylesheet" href="css/960_12_col.css" />
    <link rel="stylesheet" href="css/mainstyle.css" />
</head>
<body>
    <!-- container is a wrapper for all main sections, and defines bounds of
    any content on the screen -->
    <div id="container" class="container_12">

    	<!-- IMPORT HEADER -->
    	<?php require_once Path::partials().'header.php' ?>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align: center">
            <table>
                <tr>
                    <td><h1>Header 1</h1></td>
                    <td><h2>Header 2</h2></td>
                    <td><h3>Header 3</h3></td>
                </tr><tr>
                    <td><h4>Header 4</h4></td>
                    <td><h5>Header 5</h5></td>
                    <td><h6>Header 6</h6></td>
                </tr>
            </table>
        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once Path::partials().'footer.php' ?>

    </div>
</body>
</html>