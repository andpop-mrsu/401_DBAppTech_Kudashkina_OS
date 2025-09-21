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
    View\showMessage("Функциональность базы данных ещё не реализована.");
    View\showMessage("Список всех игр будет отображаться здесь.");
}

function replayGame($id)
{
    View\showMessage("Функциональность базы данных ещё не реализована.");
    View\showMessage("Повтор игры #$id будет отображаться здесь.");
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

        if ($result) {
            View\showMessage("Правильно!");
        } else {
            View\showMessage("Неправильно!");
        }
    }

    View\showGameResult(
        Model\isWon($gameData),
        Model\getWord($gameData)
    );

    View\showMessage("\nИгровая информация ещё не сохранена в базу данных.");
}
