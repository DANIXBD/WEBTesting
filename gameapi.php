

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Game Search</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            background-size: cover;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
</head>
<body>
<h2 id="selected-images">Selected Images</h2>
<table id="selected-images-table">
    <tr>
        <td><img></td>
        <td><img></td>
        <td><img></td>
    </tr>
    <tr>
        <td><img></td>
        <td><img></td>
        <td><img></td>
    </tr>
</table>
<button onclick="takeScreenshot()">Take screenshot</button>

<form method="GET">
    <label for="search">Search for a game:</label>
    <input type="text" name="search" id="search" required>
    <button type="submit">Search</button>
    <button type="button" id="add-images" onclick="moveSelectedImages()">Move selected images to Selected...</button>
    <button type="button" id="delete-images" onclick="deleteSelectedImages()">Remove selected images</button>
    <button type="button" id="uncheck-all" onclick="uncheckAll()">Uncheck all</button>
</form>

<script>
function updateSelectedImages() {
    var selectedImagesTable = document.querySelector("#selected-images-table");
    selectedImagesTable.innerHTML = "";
    var selectedImageUrls = JSON.parse(localStorage.getItem('selectedImageUrls')) || [];
    if (selectedImageUrls.length === 0) {
        selectedImagesTable.innerHTML = "<p>No images selected.</p>";
    } else {
        var rowCount = 0;
        var currentRow = selectedImagesTable.insertRow(rowCount);
        selectedImageUrls.forEach(function (imageUrl, index) {
            var cell = currentRow.insertCell(index % 3);
            var imgElement = document.createElement("img");
            imgElement.src = imageUrl;
            cell.appendChild(imgElement);
            if ((index + 1) % 3 === 0) {
                rowCount++;
                currentRow = selectedImagesTable.insertRow(rowCount);
            }
        });
    }
}

function takeScreenshot() {
    var table = document.querySelector("#selected-images-table");

    // Ensure images are loaded before taking the screenshot
    var images = table.querySelectorAll("img");
    var imagesLoaded = 0;

    images.forEach(function(image) {
        if (image.complete) {
            imagesLoaded++;
        } else {
            image.onload = function() {
                imagesLoaded++;
                if (imagesLoaded === images.length) {
                    captureCanvas();
                }
            };
        }
    });

    if (imagesLoaded === images.length) {
        // All images are loaded, take the screenshot immediately
        captureCanvas();
    }

    function captureCanvas() {
        html2canvas(table).then(function (canvas) {
            var dataUrl = canvas.toDataURL("image/jpg");
            var imageElement = document.createElement("img");
            imageElement.src = dataUrl;
            document.body.appendChild(imageElement);
            var linkElement = document.createElement("a");
            linkElement.href = dataUrl;
            linkElement.download = "screenshot.jpg";
            document.body.appendChild(linkElement);
            linkElement.click();
        });
    }
}

function moveSelectedImages() {
    var selectedImages = [];
    var checkboxes = document.querySelectorAll("input[name='selected[]']:checked");
    checkboxes.forEach(function (checkbox) {
        var imageUrl = checkbox.parentNode.querySelector("img").src;
        selectedImages.push(imageUrl);
    });

    var storedImages = JSON.parse(localStorage.getItem('selectedImageUrls')) || [];
    selectedImages.forEach(function (imageUrl) {
        if (!storedImages.includes(imageUrl)) {
            storedImages.push(imageUrl);
        }
    });
    localStorage.setItem('selectedImageUrls', JSON.stringify(storedImages));
    updateSelectedImages();
}

function deleteSelectedImages() {
    var storedImages = JSON.parse(localStorage.getItem('selectedImageUrls')) || [];
    var checkboxes = document.querySelectorAll("input[name='selected[]']:checked");
    checkboxes.forEach(function (checkbox) {
        var imageUrl = checkbox.parentNode.querySelector("img").src;
        storedImages = storedImages.filter(function (url) {
            return url !== imageUrl;
        });
    });
    localStorage.setItem('selectedImageUrls', JSON.stringify(storedImages));
    updateSelectedImages();
}

function uncheckAll() {
    var checkboxes = document.querySelectorAll("input[name='selected[]']");
    checkboxes.forEach(function (checkbox) {
        checkbox.checked = false;
    });
}

window.onload = function () {
    updateSelectedImages();
};

</script>
</body>
</html>

apikey=  1038c4c9d6004da5949fe27247209c10
 <?php

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_url = 'https://api.rawg.io/api/games?key=1038c4c9d6004da5949fe27247209c10&search=' . urlencode($search);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response);
        showData($data);
    } else {
        echo "Error: Unable to retrieve data from the API.";
    }
}

function showData($data)
{
    $selected_images = array();
    if (isset($_GET['selected'])) {
        $selected_images = explode(',', $_GET['selected']);
    }

    echo "<form method='GET'>";
    echo "<table border='1'>";

    foreach ($data->results as $game) {
        echo "<tr style='background-image: url(" . $game->background_image . "); background-size: cover'>";
        echo "<td>";
        echo "<strong>Name: </strong>" . $game->name . "<br>";
        echo "<strong>Platforms: </strong>";
        foreach ($game->platforms as $platform) {
            echo $platform->platform->name . ", ";
        }
        echo "<br>";
        echo "<strong>Release Date: </strong>" . $game->released . "<br>";
        echo "<strong>Rating: </strong>" . $game->rating . "<br>";
        echo "<strong>Genres: </strong>";
        foreach ($game->genres as $genre) {
            echo $genre->name . ", ";
        }

        echo "<div class='game-screenshots'>";
        foreach ($game->short_screenshots as $screenshot) {
            $checked = in_array($screenshot->id, $selected_images) ? 'checked' : '';
            echo "<label>";
            echo "<input type='checkbox' name='selected[]' value='" . $screenshot->id . "' $checked>";
            echo "<img src='" . $screenshot->image . "' width='100' height='100'>";
            echo "</label>";
        }
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";
    echo "<button type='submit'>Update Selection</button>";
    echo "</form>";

    if (!empty($selected_images)) {
        echo "<h2>Selected Images</h2>";
        echo "<table id='selected-images-table' border='1'>";
        $row_count = 0;
        foreach ($data->results as $game) {
            foreach ($game->short_screenshots as $screenshot) {
                if (in_array($screenshot->id, $selected_images)) {
                    if ($row_count % 3 == 0) {
                        echo "<tr>";
                    }
                    echo "<td><img src='" . $screenshot->image . "' width='100' height='100'></td>";
                    $row_count++;
                    if ($row_count % 3 == 0) {
                        echo "</tr>";
                    }
                }
            }
        }
        echo "</table>";
    }
}
?>