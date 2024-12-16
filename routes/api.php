<?php

use App\Http\Controllers\PokeController;
use App\Http\Middleware\tokensMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Rota de registro na pokedex
Route::post('/signup', [PokeController::class, 'signup']);
//Rota de login na pokedex
Route::post('/signin', [PokeController::class, 'signin']);


Route::middleware(tokensMiddleware::class)->group(function () {
    //Dados do treinador logado na pokedex
    Route::get('/trainer/data', [PokeController::class, 'dataTrainer']);
    //Rota de logout para sair da pokedex
    Route::get('/logout', [PokeController::class, 'logout']);
    //Rota de leitura ou atualização de dados do pokémon
    Route::post('pokemon/read', [PokeController::class, 'read']);
    //Rota de listagem de pokemons
    Route::get('pokemon/list', [PokeController::class, 'list']);
    //Rota de consulta de pokemons
    Route::post('pokemon/view', [PokeController::class, 'view']);
});

