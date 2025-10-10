<?php

namespace olya2004\hangman\View;

const HANGMAN_PICS = [
    "
    +---+
        |
        |
        |
       ===",
    "
    +---+
    0   |
        |
        |
       ===",
    "
    +---+
    0   |
    |   |
        |
       ===",
    "
    +---+
    0   |
   /|   |
        |
       ===",
    "
    +---+
    0   |
   /|\  |
        |
       ===",
    "
    +---+
    0   |
   /|\  |
   /    |
       ===",
    "
    +---+
    0   |
   /|\  |
   / \  |
       ==="
];

function showHelp()
{
    \cli\line("Hangman Game");
    \cli\line("============");
    \cli\line("Usage:");
    \cli\line("  hangman [options]");
    \cli\line("");
    \cli\line("Options:");
    \cli\line("  --new, -n        Start new game (default)");
    \cli\line("  --list, -l       Show all saved games");
    \cli\line("  --replay ID, -r  Replay game by ID");
    \cli\line("  --help, -h       Show this help");
    \cli\line("");
    \cli\line("Examples:");
    \cli\line("  hangman");
    \cli\line("  hangman --new");
    \cli\line("  hangman --list");
    \cli\line("  hangman --replay 5");
}

function askPlayerName()
{
    \cli\line("Welcome to Hangman!");
    \cli\line("===================");
    echo "Введите своё имя: ";
    $handle = fopen("php://stdin", "r");
    $name = trim(fgets($handle));
    fclose($handle);
    return $name;
}

function askLetter()
{
    echo "Введите букву: ";
    $handle = fopen("php://stdin", "r");
    $letter = trim(fgets($handle));
    fclose($handle);
    return $letter;
}

function showGameState($maskedWord, $usedLetters, $errors)
{
    \cli\line(HANGMAN_PICS[$errors]);
    \cli\line("");
    \cli\line("Слово: " . $maskedWord);
    \cli\line("Использованные буквы: " . implode(', ', $usedLetters));
    \cli\line("Осталось попыток: " . (6 - $errors));
    \cli\line("");
}

function showMessage($message)
{
    \cli\line($message);
}

function showGameResult($won, $word)
{
    \cli\line("");
    \cli\line("=====================================");
    if ($won) {
        \cli\line("🎉 Поздравляем! Вы выиграли! 🎉");
    } else {
        \cli\line("💀 Игра окончена! Вы проиграли! 💀");
    }
    \cli\line("The word was: " . $word);
    \cli\line("=====================================");
}
