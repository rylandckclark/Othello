$(document).ready(function()
{
    $("#newGame").click(function ()
    {
        NewGame();
    });

    $("#quitGame").click(function ()
    {
        QuitGame();
    });

    $("#gameGrid").on("click","td", function()
    {
        HandleClick(this.id);
    });
});

function HandleClick(id)
{
    let coords = id.split(',');
    let data = {};

    data["action"] = "click";
    data["x"] = coords[0];
    data["y"] = coords[1];

    CallAJAX("gameplay.php", "get", data, "json", HandleClickSuccess, ErrorMethod);
}

/* Function: NewGame */
/* Purpose: Generates an ajax call to handle all functions of a game start */
/* Inputs: None */
/* Returns: None */
function NewGame()
{
    let data = {};

    data["action"] = "newGame";
    data["player1"] = $("#player1").val();
    data["player2"] = $("#player2").val();

    CallAJAX("gameplay.php", "get", data, "json", NewGameSuccess, ErrorMethod);
}

/* Function: QuitGame */
/* Purpose: Selects a random player to be X */
/* Inputs: None */
/* Returns: None */
function QuitGame()
{
    let data = {};

    data["action"] = "quitGame";

    CallAJAX("gameplay.php", "get", data, "json", QuitGameSuccess, ErrorMethod);
}

function CheckWin()
{
    let data = {};

    data["action"] = "CheckWin";

    CallAJAX("gameplay.php", "get", data, "json", CheckWinSuccess, ErrorMethod);
}

/* Function: CallAJAX */
/* Purpose: Generates an ajax call to the page with the needed data */
/* Inputs: url, method, data, dataType, successMethod, errorMethod */
/* Returns: None */
function CallAJAX(url, method, data, dataType, successMethod, errorMethod)
{
    let options = {}; //Make sure these are objects
    options["url"] = url;
    options["method"] = method;
    options["data"] = data;
    options["dataType"] = dataType;
    options["success"] = successMethod;
    options["error"] = errorMethod;

    //Make the AJAX call
    $.ajax(options);
}

//SUCCESS FUNCTIONS

/* Function: QuitGame */
/* Purpose: Removes the background, and hides the gameGrid */
/* Inputs: returnedData, returnedStatusAJAX, sentRequest */
/* Returns: None */
function QuitGameSuccess(returnedData, returnedStatusAJAX, sentRequest)
{
    //Clear the gameGrid
    $("#gameGrid").html("");
    //Reset the game count message
    $("#gameCount").html("");
    //Change the status message
    $("#status").html("Enter your names below:");
    //Clear the player name's
    $("#player1").val("");
    $("#player2").val("");
}

function CheckWinSuccess(returnedData)
{
    if(returnedData["WinMessage"] != undefined)
    {
        $("#status").html(returnedData["WinMessage"]);
    }
}

function NewGameSuccess(returnedData, returnedStatusAJAX, sentRequest)
{
    gameBoard = returnedData["GameState"];
    $("#gameCount").html(returnedData["GameCount"]);
    $("#status").html(returnedData["status"]);
    DrawGameboard(gameBoard);
    validMoves = returnedData["ValidMoves"];
    //Don't check the object if it is not defined
    if(validMoves != undefined) {
        //For all the data entries in the object validMoves
        Object.entries(validMoves).forEach(([key, subArray]) => {
            subArray.forEach((value) => {
                //Apparently JavaScript treats keys like a string so make sure it is a number
                //Pull the valid move tile by it's ID, denoted from validMoves[key][subArray] is treated similarily to validMoves[1](x-position)...
                //...[subArray](this exists as a seperate array with all the values at the x-position) so instead of [1][5], [1][6], [1][7] it is treated like...
                //... [1][5,6,7] which is why it is neccessary to iterate through the subArray
                key = Number(key);
                square = $("[id='" + key + "," + value + "']");
                //Darker tile indicates where it is okay to place a chip
                square.css("background-color","#063600");
            });
        });
    }
}

function HandleClickSuccess(returnedData, returnedStatusAJAX, sentRequest)
{
    //Gameboard to be iterated through
    gameBoard = returnedData["GameState"];
    //Show how many squares both colors occupy
    $("#gameCount").html(returnedData["GameCount"]);
    //Drawing function
    DrawGameboard(gameBoard);
    //Number of valid moves show the squares you can click 
    validMoves = returnedData["ValidMoves"];
    //Don't check the object if it is not defined
    if(validMoves != undefined) {
        //For all the data entries in the object validMoves
        Object.entries(validMoves).forEach(([key, subArray]) => {
            subArray.forEach((value) => {
                //Apparently JavaScript treats keys like a string so make sure it is a number
                //Pull the valid move tile by it's ID, denoted from validMoves[key][subArray] is treated similarily to validMoves[1](x-position)...
                //...[subArray](this exists as a seperate array with all the values at the x-position) so instead of [1][5], [1][6], [1][7] it is treated like...
                //... [1][5,6,7] which is why it is neccessary to iterate through the subArray
                key = Number(key);
                square = $("[id='" + key + "," + value + "']");
                //Darker tile indicates where it is okay to place a chip
                square.css("background-color","#063600");
            });
        });
    }
    //In the event that a player can't place down a chip
    //Communicate the next turn
    $("#status").html(returnedData["statusmsg"]);

    //Check to see if the game is over
    CheckWin();
}

//ERROR FUNCTIONS

/* Function: Error Method */
/* Purpose: Console Log's Relevant Error Information */
/* Inputs: sentRequest, returnedStatusAJAX, errorThrown */
/* Returns: None */
function ErrorMethod(sentRequest, returnedStatusAJAX, errorThrown)
{
    console.log(sentRequest);
    console.log(returnedStatusAJAX);
    console.log(errorThrown);
}

function DrawGameboard(gameBoard)
{
    $("#gameGrid").html("");
    gameTable = $("<table id =\"gameTable\"></table>");
    gameBoard.forEach((subArray, index) => {
        row = $("<tr></tr>");
        subArray.forEach((pos, yindex) => {
            if(pos == 'G')
            {
                row.append("<td id=\"" + index + ',' + yindex + "\" style = \"background-image: none\"></td>");
            }
            if(pos == 'B')
            {
                row.append("<td id=\"" + index + ',' + yindex + "\" style =\"background-image:  url(./images/blackchip.png)\"></td>");
            }
            if(pos == 'W')
            {
                row.append("<td id=\"" + index + ',' + yindex + "\" style = \"background-image: url(./images/whitechip.png)\"></td>");
            }
        });
        gameTable.append(row);
    });

    //Put the game table in the middle
    $("#gameGrid").append(gameTable);
}