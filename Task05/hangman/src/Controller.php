<?php

namespace olya2004\hangman\Controller;

use olya2004\hangman\Model;
use olya2004\hangman\View;

function run(array $argv)
{
    $args = new \cli\Arguments();
    $args->addFlag(['new', 'n'], 'Start new game');
    $args->addFlag(['list', 'l'], 'Show all saved games');
    $args->addOption(['replay', 'r'], [
        'default' => null,
        'description' => 'Replay game by ID'
    ]);
    $args->addFlag(['help', 'h'], 'Show help');

    $args->parse($argv);

    if ($args['help']) {
        showHelp();
        return;
    }

    if ($args['list']) {
        showList();
        return;
    }

    if ($args['replay']) {
        replayGame($args['replay']);
        return;
    }

    startNewGame();
}

function showHelp()
{
    View\showHelp();
}

function showList()
{
    $db = new \olya2004\hangman\Database();
    $games = $db->listGames();

    if (empty($games)) {
        View\showMessage("Нет сохранённых игр.");
        return;
    }

    foreach ($games as $game) {
        View\showMessage(sprintf(
            "ID: %d | Игрок: %s | Слово: %s | Результат: %s | Дата: %s",
            $game['id'], $game['player_name'], $game['word'], $game['result'], $game['game_date']
        ));
    }
}

function replayGame($id)
{
    $db = new \olya2004\hangman\Database();
    $game = $db->loadGame((int)$id);

    if (!$game) {
        View\showMessage("Игра с ID #$id не найдена.");
        return;
    }

    View\showMessage("Повтор игры #$id");
    View\showMessage("Игрок: {$game['game']['player_name']}, Слово: {$game['game']['word']}, Дата: {$game['game']['game_date']}");
    View\showMessage("Исход: " . ($game['game']['result'] === 'won' ? 'Выиграл' : 'Проиграл'));
    View\showMessage("Попытки:");

    $usedLetters = [];
    $errors = 0;
    $word = $game['game']['word'];

    foreach ($game['attempts'] as $attempt) {
        $letter = $attempt['letter'];
        $usedLetters[] = $letter;
        if ($attempt['result'] === 'wrong') {
            $errors++;
        }

        $maskedWord = '';
        for ($i = 0; $i < strlen($word); $i++) {
            $maskedWord .= in_array($word[$i], $usedLetters) ? $word[$i] : '_';
            $maskedWord .= ' ';
        }

        View\showGameState(trim($maskedWord), $usedLetters, $errors);
        View\showMessage("Ход {$attempt['attempt_number']}: буква '{$letter}' — {$attempt['result']}");
    }
}

function startNewGame()
{
    $playerName = View\askPlayerName();
    $gameData = Model\initGame($playerName);

    while (!Model\isGameOver($gameData)) {
        View\showGameState(
            Model\getMaskedWord($gameData),
            Model\getUsedLetters($gameData),
            Model\getErrorsCount($gameData)
        );

        $letter = View\askLetter();

        if (!Model\isValidLetter($letter)) {
            View\showMessage("Пожалуйста введите одну букву!");
            continue;
        }

        if (Model\isLetterUsed($gameData, $letter)) {
            View\showMessage("Буква уже использована!");
            continue;
        }

        $result = Model\guessLetter($gameData, $letter);

        View\showMessage($result ? "Правильно!" : "Неправильно!");
    }

    $won = Model\isWon($gameData);
    View\showGameResult($won, Model\getWord($gameData));

    $db = new \olya2004\hangman\Database();
    
    $db->saveGame(
        $gameData['playerName'], // playerName
        $gameData['word'],       // word
        $won ? 'won' : 'lost',   // result
        $gameData['attempts']    // attempts
    );

    View\showMessage("Игра сохранена в базу данных.");
}