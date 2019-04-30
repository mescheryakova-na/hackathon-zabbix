<?php
$planetRelativeCoords = [
   'dathomir' => ['x' => 64.3, 'y' => 28.2],
   'dagobah' => ['x' => 27, 'y' => 80.2],
   'dantooine' => ['x' => 65.9, 'y' => 22],
   'mon-calamari' => ['x' => 88.1, 'y' => 45.2],
   'kamino' => ['x' => 48.1, 'y' => 82.8],
   'apk1' => ['x' => 15, 'y' => 25],
   'lwhekk' => ['x' => 10.6, 'y' => 51.6],
   'luprora' => ['x' => 20, 'y' => 20],
   'kpibinom3' => ['x' => 25, 'y' => 15],
   'ambria' => ['x' => 60.1, 'y' => 44.7],
];
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <link href="/css/main.css" rel="stylesheet">
    </head>
    <body>
        <div id="map">
            <div class="image">
                <img src="/images/map.jpg" />
            </div>
        </div>
        <script src="/js/jquery.js"></script>
        <script src="/js/main.js"></script>
        <script>
            planetRelativeCoords = <?= json_encode($planetRelativeCoords);?>;
        </script>
    </body>
</html>

