<?php

namespace olya2004\hangman\Model;

const MAX_ERRORS = 6;
const WORDS = [
    'PYTHON', 'JAVASCRIPT', 'KOTLIN', 'GOLANG',
    'DOCKER', 'GITHUB', 'UBUNTU', 'FEDORA',
    'CHROME', 'FIREFOX', 'SAFARI', 'VISUAL'
];

function initGame($playerName)
{
    return [
        'playerName' => $playerName,
        'word' => WORDS[array_rand(WORDS)],
        'usedLetters' => [],
        'errorsCount' => 0,
        'attempts' => [],
        'gameDate' => date('Y-m-d H:i:s')
    ];
}

function guessLetter(&$gameData, $letter)
{
    $letter = strtoupper($letter);
    $gameData['usedLetters'][] = $letter;

    $isCorrect = strpos($gameData['word'], $letter) !== false;

    if (!$isCorrect) {
        $gameData['errorsCount']++;
    }

    $gameData['attempts'][] = [
        'attempt' => count($gameData['attempts']) + 1,
        'letter' => $letter,
        'result' => $isCorrect ? 'correct' : 'wrong'
    ];

    return $isCorrect;
}

function getMaskedWord($gameData)
{
    $masked = '';
    $word = $gameData['word'];
    $usedLetters = $gameData['usedLetters'];

    for ($i = 0; $i < strlen($word); $i++) {
        $char = $word[$i];
        $masked .= in_array($char, $usedLetters) ? $char : '_';
        $masked .= ' ';
    }

    return trim($masked);
}

function isGameOver($gameData)
{
    return isWon($gameData) || isLost($gameData);
}

function isWon($gameData)
{
    $word = $gameData['word'];
    $usedLetters = $gameData['usedLetters'];

    for ($i = 0; $i < strlen($word); $i++) {
        if (!in_array($word[$i], $usedLetters)) {
            return false;
        }
    }
    return true;
}

function isLost($gameData)
{
    return $gameData['errorsCount'] >= MAX_ERRORS;
}

function isValidLetter($input)
{
    return preg_match('/^[a-zA-Z]$/', $input) === 1;
}

function isLetterUsed($gameData, $letter)
{
    return in_array(strtoupper($letter), $gameData['usedLetters']);
}

function getWord($gameData)
{
    return $gameData['word'];
}

function getUsedLetters($gameData)
{
    return $gameData['usedLetters'];
}

function getErrorsCount($gameData)
{
    return $gameData['errorsCount'];
}

function getPlayerName($gameData)
{
    return $gameData['playerName'];
}

function getGameDate($gameData)
{
    return $gameData['gameDate'];
}

function getAttempts($gameData)
{
    return $gameData['attempts'];
}

function getGameResult($gameData)
{
    return isWon($gameData) ? 'won' : 'lost';
}
