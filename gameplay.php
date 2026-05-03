<?php
//Start the session
session_start();

//Create an array to clean the data
$clean = array();

//Clean the data by stripping the tags and special characters
foreach($_GET as $key => $value)
{
    $clean[trim(strip_tags(htmlspecialchars($key)))] = trim(strip_tags(htmlspecialchars($value)));
}

//Output to be json encoded and shot back into javascript
$output = array();

//If the action is set
if(isset($clean["action"]))
{
    if($clean["action"] == "newGame")
    {
        if(CheckNames()){ RandomPlayerSelection(); GameCount(); ShowValidMoves();}
    }
    if($clean["action"] == "quitGame")
    {
        QuitGame();
    }
    if($clean["action"] == "click")
    {
        HandleClick($clean["x"],$clean["y"]); GameCount();
    }
    if($clean["action"] == "CheckWin")
    {
        CheckWin();
    }
}

$output["GameCount"] = $_SESSION["GameCount"];

echo json_encode($output);

die();

/***********************************************************************************************************************************************************************/
/************************************************************************FUNCTIONS**************************************************************************************/
/***********************************************************************************************************************************************************************/

/* Function: CheckNames */
/* Purpose: Checks both names to see if they are valid */
/* Inputs: None */
/* Returns: None */
function CheckNames()
{
    global $output, $clean;

    //Is player 1 valid?
    if(isset($clean["player1"]) && strlen($clean["player1"]) > 0)
    {
        //If player 2 is valid, then encode the names in the output array
        if(isset($clean["player2"]) && strlen($clean["player2"]) > 0)
        {
            $_SESSION["Player1Name"] = $clean["player1"];
            $_SESSION["Player2Name"] = $clean["player2"];
            $output["status"] = "Both names are valid. Press \"New Game\" to begin.";
            $output["namesGood"] = 1;
            return true;
        }
        //Otherwise let the user know that they need to enter a name for player 2
        else
        {
            $output["status"] = "Please enter a valid name for player 2.";
            $output["namesGood"] = 0;
            return false;
        }
    }
    else
    {
        $output["status"] = "Please enter a valid name for player 1.";
    }
    $output["namesGood"] = 0;
    return false;
}

/* Function: RandomPlayerSelection */
/* Purpose: Selects a random player to be Black */
/* Inputs: None */
/* Returns: None */
function RandomPlayerSelection()
{
    global $output, $clean;

    $_SESSION["Player1Name"] = $clean["player1"];
    $_SESSION["Player2Name"] = $clean["player2"];

    if(rand(1,10) % 2 == 0)
    {
        $_SESSION["Turn"] = 1; 
        $output["status"] = $_SESSION["Player1Name"] . ' ' . "is \"Black\". Choose where to start";
        $_SESSION["Player1Color"] = 'B';
        $_SESSION["Player2Color"] = 'W';
    }
    else
    {
        $_SESSION["Turn"] = 2;    
        $output["status"] = $_SESSION["Player2Name"] . ' ' . "is \"Black\". Choose where to start";
        $_SESSION["Player2Color"] = 'B';
        $_SESSION["Player1Color"] = 'W';
    }

    $_SESSION["GameState"] = [
    ['G','G','G','G','G','G','G','G'],
    ['G','G','G','G','G','G','G','G'],
    ['G','G','G','G','G','G','G','G'],
    ['G','G','G','B','W','G','G','G'],
    ['G','G','G','W','B','G','G','G'],
    ['G','G','G','G','G','G','G','G'],
    ['G','G','G','G','G','G','G','G'],
    ['G','G','G','G','G','G','G','G'],
    ];

    $output["GameState"] = $_SESSION["GameState"];
}

function AllowClick($xCoords, $yCoords)
{
    $currentColor;

    if($_SESSION["Turn"] == 1)
    {
        $currentColor = $_SESSION["Player1Color"];
    }
    else
    {
        $currentColor = $_SESSION["Player2Color"];
    }

    //White can only place next to black
    if($currentColor == 'W')
    {

    }
    //Black can only place next to white
    if($currentColor == 'B')
    {

    }
}

//When calling the function pass $xCoords - 1, and $yCoords can stay the same
function CheckValidMoves($x, $y, $color)
{
    $gameBoard = $_SESSION["GameState"];

    CheckHorizontal($x, $y, $color);
}

function HandleClick($x, $y)
{
    global $output;

    if($_SESSION["Turn"] == 1){
        if(Check($x, $y, $_SESSION["Player1Color"]))
        {
            $_SESSION["GameState"][$x][$y]=$_SESSION["Player1Color"];
            FlipDisks($x,$y,$_SESSION["Player1Color"]);
            $_SESSION["Turn"] = 2;
            $output["statusmsg"] = $_SESSION["Player2Name"] . '\'s Turn (' . $_SESSION["Player2Color"] . ').';
            
        }
        else
        {
            $output["statusmsg"] = "You can't place your chip there.";
        }
    }
    else
    {
        //Check if were are in a position to flank the opponent passing $x-1 representing left $x+1 representing the boundary
        if(Check($x, $y, $_SESSION["Player2Color"]))
        {
            $_SESSION["GameState"][$x][$y]=$_SESSION["Player2Color"];
            FlipDisks($x,$y,$_SESSION["Player2Color"]);
            $_SESSION["Turn"] = 1;
            $output["statusmsg"] = $_SESSION["Player1Name"] . '\'s Turn (' . $_SESSION["Player1Color"] . ').';
        }
        else
        {
            $output["statusmsg"] = "You can't place your chip there.";
        }
    }

    $output["GameState"] = $_SESSION["GameState"];
    //Show all the valid moves
    ShowValidMoves();
}

function ShowValidMoves()
{
    global $output;

    //Player 1's turn
    if($_SESSION["Turn"] == 1)
    {
        if($_SESSION["Player1Color"] == 'W')
        {
            if(CheckWhiteValidMoves() == 0)
            {
                $output["statusmsg"] = "No valid moves. Switching turns. ";
                $output["statusmsg"] .= $_SESSION["Player2Name"] . '\'s Turn (' . $_SESSION["Player2Color"] . ').';
                $_SESSION["Turn"] = 2;
                CheckBlackValidMoves();
            }
        }
        else
        {
            if(CheckBlackValidMoves() == 0)
            {
                $output["statusmsg"] = "No valid moves. Switching turns. ";
                $output["statusmsg"] .= $_SESSION["Player2Name"] . '\'s Turn (' . $_SESSION["Player2Color"] . ').';
                $_SESSION["Turn"] = 2;
                CheckWhiteValidMoves();
            }
        }
    }
    //Player 2's turn
    else
    {
        if($_SESSION["Player2Color"] == 'W')
        {
            if(CheckWhiteValidMoves() == 0)
            {
                $output["statusmsg"] = "No valid moves. Switching turns. ";
                $output["statusmsg"] .= $_SESSION["Player1Name"] . '\'s Turn (' . $_SESSION["Player1Color"] . ').';
                $_SESSION["Turn"] = 1;
                CheckBlackValidMoves();
            }
        }
        else
        {
            if(CheckBlackValidMoves() == 0)
            {
                $output["statusmsg"] = "No valid moves. Switching turns. ";
                $output["statusmsg"] .= $_SESSION["Player1Name"] . '\'s Turn (' . $_SESSION["Player1Color"] . ').';
                $_SESSION["Turn"] = 1;
                CheckWhiteValidMoves();
            }
        }
    }

    $output["ValidMoves"] = $_SESSION["ValidMoves"];
}

function CheckBlackValidMoves()
{
    global $output;

    //View of the gameboard to be iterated through
    $gameBoard = $_SESSION["GameState"];

    //Number of valid moves, will return as zero if there are none
    $validMoves = 0; 

    //Array to store the x and y values of the valid move squares in order to handle a drawing feature
    $moves = array();

    //Check X and Y coordinates of the whole board
    for($x = 0; $x < 8; $x++)
    {
        for($y = 0; $y < 8; $y++)
        {
            if($_SESSION["GameState"][$x][$y] == 'G')
            {
                if(Check($x,$y,'B'))
                {
                    $validMoves++;
                    $moves[$x][] = $y;
                }
            }
        }
    }

    $_SESSION["ValidMoves"] = $moves;

    return $validMoves;
}

function CheckWhiteValidMoves()
{
    //Global output variable to stash output values
    global $output;

    //View of the gameboard to be iterated through
    $gameBoard = $_SESSION["GameState"];

    //Number of valid moves, will return as zero if there are none
    $validMoves = 0; 

    //Array to store the x and y values of the valid move squares in order to handle a drawing feature
    $moves = array();

    //Check X and Y coordinates of the whole board
    for($x = 0; $x < 8; $x++)
    {
        for($y = 0; $y < 8; $y++)
        {
            if($_SESSION["GameState"][$x][$y] == 'G')
            {
                if(Check($x,$y,'W'))
                {
                    $validMoves++;
                    $moves[$x][] = $y;
                }
            }
        }
    }

    //Store the $moves array to send it back to JavaScript
    $_SESSION["ValidMoves"] = $moves;

    //Return the number of valid moves
    return $validMoves;
}

function Check($x, $y, $color)
{
    if($_SESSION["GameState"][$x][$y] != 'G')
    {
        return false;
    }
    if(CheckLeft($x-1, $y, 0, $color))
    {
        return true;
    }
    if(CheckRight($x+1, $y, 0, $color))
    {
        return true;
    }
    if(CheckUp($x, $y-1, 0, $color))
    {
        return true;
    }
    if(CheckDown($x, $y+1, 0, $color))
    {
        return true;
    }
    if(CheckDiagonalRightUp($x+1, $y-1, 0, $color))
    {
        return true;
    }
    if(CheckDiagonalLeftUp($x-1, $y-1, 0, $color))
    {
        return true;
    }
    if(CheckDiagonalRightDown($x+1,$y+1,0,$color))
    {
        return true;
    }
    if(CheckDiagonalLeftDown($x-1,$y+1,0, $color))
    {
        return true;
    }
    return false;
}

function FlipDisks($x, $y, $color)
{
    //FLANKING IS ONLY CONSIDERED ON A DIAGONAL PLACEMENT IF THERE IS A CHIP OF THAT SAME COLOR ON THE OTHER END OF THE FLANK
    //FLANKS DO NOT JUMP ACROSS YOUR OWN CHIP
    //FLIP IF OUTFLANKING ON DIAGONAL and check all directions 
    //YOU CAN OUTFLANK IN MULTIPLE DIRECTIONS
    //RIGHT NOW IT IS FLIPPING EVEN WHEN THERE IS NOT AN OPOSSITE COLOR ON THE OTHER END
    if(CheckRight($x+1, $y, 0, $color))
    {
        FlipRight($x+1, $y, $color);
    }
    if(CheckLeft($x-1, $y, 0, $color))
    {
        FlipLeft($x-1, $y, $color);
    }
    if(CheckUp($x, $y-1, 0,$color))
    {
        FlipUp($x, $y-1, $color);
    }
    if(CheckDown($x, $y+1,0,$color))
    {
        FlipDown($x, $y+1, $color);
    }
    if(CheckDiagonalLeftUp($x-1, $y-1,0,$color))
    {
        FlipDiagonalLeftUp($x-1, $y-1, $color);
    }
    if(CheckDiagonalLeftDown($x-1, $y+1,0,$color))
    {
        FlipDiagonalLeftDown($x-1, $y+1, $color);
    }
    if(CheckDiagonalRightUp($x+1, $y-1,0,$color))
    {
        FlipDiagonalRightUp($x+1, $y-1, $color);
    }
    if(CheckDiagonalRightDown($x+1, $y+1,0,$color))
    {
        FlipDiagonalRightDown($x+1, $y+1, $color);
    }
}

function FlipRight($x, $y, $color)
{
    //BASE CASES
    if($x > 7)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipRight($x+1, $y, $color);
}

function FlipLeft($x, $y, $color)
{
    //BASE CASES
    if($x < 0)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipLeft($x-1, $y, $color);
}

function FlipUp($x, $y, $color)
{
    //BASE CASES
    if($y < 0)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipUp($x, $y-1, $color);
}

function FlipDown($x, $y, $color)
{
    //BASE CASES
    if($y > 7)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipDown($x, $y+1, $color);
}

function FlipDiagonalRightUp($x,$y,$color)
{
    if($y < 0 || $x > 7)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipDiagonalRightUp($x+1, $y-1, $color);
}

function FlipDiagonalLeftUp($x, $y, $color)
{
    if($x < 0 || $y < 0)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipDiagonalLeftUp($x-1, $y-1, $color);
}

function FlipDiagonalLeftDown($x, $y, $color)
{
    if($x < 0 || $y > 7)
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipDiagonalLeftDown($x-1, $y+1, $color);
}

function FlipDiagonalRightDown($x,$y,$color)
{
    if($y > 7 || $x > 7)
    {
        return;
    }
    if($_SESSION["GameState"][$x][$y] == $color)
    {
        return true;
    }
    if($_SESSION["GameState"][$x][$y] == 'G')
    {
        return false;
    }
    if($_SESSION["GameState"][$x][$y] != $color)
    {
        $_SESSION["GameState"][$x][$y] = $color;
    }
    //RECURSIVE CALL
    return FlipDiagonalRightDown($x+1, $y+1, $color);
}

//Check if there's a flank position to the right
function CheckRight($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($x > 7)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckRight($x+1, $y, ++$index, $color);
    
}

//Check if there's a flank position to the left
function CheckLeft($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($x < 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckLeft($x-1, $y, ++$index, $color);
    
}

//Check if there is a flank position above
function CheckUp($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($y < 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckUp($x, $y-1, ++$index, $color);
}

//Check if there's a flank position below
function CheckDown($x , $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($y > 7)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckDown($x, $y+1, ++$index, $color);
}

function CheckDiagonalRightUp($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($y < 0 || $x > 7)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckDiagonalRightUp($x+1, $y-1, ++$index, $color);
}

function CheckDiagonalLeftUp($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($y < 0 || $x < 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckDiagonalLeftUp($x-1, $y-1, ++$index, $color);
}

function CheckDiagonalRightDown($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($y > 7 || $x > 7)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckDiagonalRightDown($x+1, $y+1, ++$index, $color);
}

function CheckDiagonalLeftDown($x, $y, $index, $color)
{
    $gameBoard = $_SESSION["GameState"];

    if($y > 7 || $x < 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index == 0)
    {
        return false;
    }
    if($gameBoard[$x][$y] == $color && $index >= 1)
    {
        return true;
    }
    if($gameBoard[$x][$y] == 'G')
    {
        return false;
    }
    return CheckDiagonalLeftDown($x-1, $y+1, ++$index, $color);
}

function GameCount()
{
    $white = 0;
    $black = 0;

    for($x = 0; $x < 8; $x++)
    {
        for($y = 0; $y < 8; $y++)
        {
            if($_SESSION["GameState"][$x][$y] == 'B')
            {
                $black++;
            }
            if($_SESSION["GameState"][$x][$y] == 'W')
            {
                $white++;
            }
        }
    }

    //Keep track of how many white chips are placed
    $_SESSION["White"] = $white;

    //Keep track of how many black chips are placed
    $_SESSION["Black"] = $black;

    //String representing how many squares black & white occupy
    $_SESSION["GameCount"] = "White: " . $white . " Black: " . $black;
}

function QuitGame()
{
    //Kills all session variables
    session_destroy();
}

function CheckWin()
{
    global $output;

    //Double-check to see if game count is correct
    GameCount();

    error_log(json_encode(CheckBlackValidMoves() == 0 && CheckWhiteValidMoves() == 0));

    if(CheckBlackValidMoves() == 0 && CheckWhiteValidMoves() == 0) {

        if($_SESSION["White"] == $_SESSION["Black"])
        {
            $output["WinMessage"] = "Tie... Nobody Wins.";
            return;
        }
        if($_SESSION["White"] > $_SESSION["Black"])
        {
            if($_SESSION["Player1Color"] == 'W') {
            $output["WinMessage"] = "White " . $_SESSION["Player1Name"] . " Wins!"; }
            else {
            $output["WinMessage"] = "White " . $_SESSION["Player2Name"] . " Wins!"; }
        }
        else
        {
            if($_SESSION["Player1Color"] == 'B') {
            $output["WinMessage"] = "Black " . $_SESSION["Player1Name"] . " Wins!"; }
            else {
            $output["WinMessage"] = "Black " . $_SESSION["Player2Name"] . " Wins!";}
        }
    }
}