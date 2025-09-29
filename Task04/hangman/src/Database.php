<?php

namespace olya2004\hangman;

class Database
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO("sqlite:" . __DIR__ . "/../bin/hangman.db");
        $this->createTables();
    }

    private function createTables()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player_name TEXT NOT NULL,
                word TEXT NOT NULL,
                result TEXT CHECK(result IN ('won', 'lost')) NOT NULL,
                game_date TEXT NOT NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                game_id INTEGER NOT NULL,
                attempt_number INTEGER NOT NULL,
                letter TEXT NOT NULL,
                result TEXT CHECK(result IN ('correct', 'wrong')) NOT NULL,
                FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE
            )
        ");
    }

    public function saveGame(array $gameData): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO games (player_name, word, result, game_date)
            VALUES (:player_name, :word, :result, :game_date)
        ");
        $stmt->execute([
            'player_name' => $gameData['playerName'],
            'word' => $gameData['word'],
            'result' => $gameData['result'],
            'game_date' => $gameData['gameDate']
        ]);

        $gameId = $this->pdo->lastInsertId();

        $stmtAttempt = $this->pdo->prepare("
            INSERT INTO attempts (game_id, attempt_number, letter, result)
            VALUES (:game_id, :attempt_number, :letter, :result)
        ");

        foreach ($gameData['attempts'] as $attempt) {
            $stmtAttempt->execute([
                'game_id' => $gameId,
                'attempt_number' => $attempt['attempt'],
                'letter' => $attempt['letter'],
                'result' => $attempt['result']
            ]);
        }

        return $gameId;
    }

    public function listGames(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, player_name, word, result, game_date
            FROM games
            ORDER BY game_date DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getGameById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$id]);
        $game = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$game) {
            return null;
        }

        $stmtAttempts = $this->pdo->prepare("SELECT * FROM attempts WHERE game_id = ? ORDER BY attempt_number ASC");
        $stmtAttempts->execute([$id]);
        $game['attempts'] = $stmtAttempts->fetchAll(\PDO::FETCH_ASSOC);

        return $game;
    }
}
