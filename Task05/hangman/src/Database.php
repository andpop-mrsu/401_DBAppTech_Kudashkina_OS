<?php

namespace olya2004\hangman;

use RedBeanPHP\R as R;

class Database
{
    public function __construct()
    {
        if (!R::testConnection()) {
            R::setup('sqlite:' . __DIR__ . '/../bin/hangman.db');
            R::useFeatureSet('novice/latest');
        }
    }

    public function listGames(): array
    {
        return R::getAll('SELECT * FROM games ORDER BY game_date DESC');
    }

    public function getGameList(): array
    {
        return R::getAll('SELECT id, player_name, word, result, game_date FROM games ORDER BY game_date DESC');
    }

    public function loadGame(int $id): ?array
    {
        $game = R::load('games', $id);
        if (!$game->id) {
            return null;
        }

        $attempts = R::findAll('attempts', 'game_id = ? ORDER BY attempt_number', [$id]);

        return [
            'game' => $game->export(),
            'attempts' => R::exportAll($attempts)
        ];
    }

    public function getPlayerName(int $id): ?string
    {
        return R::getCell('SELECT player_name FROM games WHERE id = ?', [$id]);
    }

    public function getWord(int $id): ?string
    {
        return R::getCell('SELECT word FROM games WHERE id = ?', [$id]);
    }

    public function getResult(int $id): ?string
    {
        return R::getCell('SELECT result FROM games WHERE id = ?', [$id]);
    }

    public function saveGame(string $playerName, string $word, string $result, array $attempts): int
    {
        $game = R::dispense('games');
        $game->player_name = $playerName;
        $game->word = $word;
        $game->result = $result;
        $game->game_date = date('Y-m-d H:i:s');

        $gameId = R::store($game);

        foreach ($attempts as $attempt) {
            $attemptBean = R::dispense('attempts');
            $attemptBean->game_id = $gameId;
            $attemptBean->attempt_number = $attempt['attempt'];
            $attemptBean->letter = $attempt['letter'];
            $attemptBean->result = $attempt['result'];
            R::store($attemptBean);
        }

        return $gameId;
    }

    public function saveGameFromModel(Model $model, string $result): int
    {
        return $this->saveGame(
            $model->getPlayerName(),
            $model->getWord(),
            $result,
            $model->getAttempts()
        );
    }
}
