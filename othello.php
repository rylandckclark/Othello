<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="othello.js"></script>
    <title>Othello</title>
</head>
<body id = "pageGrid">
    <header id = "mainHeader">
        <h1>CMPE2550 - Lab1 - Othello</h1>
    </header>
    <main id = "middleGrid">
        <div id = "gameSection">
            <div id = "left">
                <p></p>
            </div>
            <div id = "players">
                <label for="player1" id = "status">Enter your names below:</label>
                <p id = "gameCount"></p>
                <div id = "playerdiv">
                    <input type="text" name="player1" id="player1" placeholder="Player 1 name goes here!">
                </div>
                <div id = "playerdiv">
                    <input type="text" name="player2" id="player2" placeholder="Player 2 name goes here!">
                </div>
                <div id = "startQuit">
                    <button type="button" id ="newGame" name="newGame">New Game</button>
                </div>
                <div id = "startQuit">
                    <button type="button" id ="quitGame" name="quitGame">Quit Game</button>
                </div>
            </div>
            <div id = "gameGrid">
                
            </div>
            <div id = "right">
                <p></p>
            </div>
        </div>
        <footer id = "footer">
            &copy; Ryland Clark 2026
            <br>
            <span id = "lastModified"><span id = "lastModified"></span>
            <script>document.getElementById('lastModified').textContent = "Last Modified:" + ' ' + document.lastModified</script></span>
        </footer>
    </main>
</body>
</html>