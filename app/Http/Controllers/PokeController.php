<?php

namespace App\Http\Controllers;

use App\Models\Ability;
use App\Models\Egg;
use App\Models\Evolution;
use App\Models\Pokemon;
use App\Models\Type;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\New_;

class PokeController extends Controller
{
    public function signup(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'username' => 'required',
            'password' => 'required',
            'city' => 'required',
            'birthdate' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json(['message' => 'Não foi possível realizar seu cadastro na Pokédex devido à falta de informações'], 422);
        }

        $validated = Validator::make($request->all(), [
            'name' => 'string',
            'lastname' => 'string',
            'username' => 'string',
            'password' => 'string',
            'birthdate' => 'date_format:Y-m-d',
            'city' => 'string'
        ]);

        if ($validated->fails()) {
            return response()->json(['message' => 'Não foi possível realizar seu cadastro na Pokédex devido a informações conflitantes de tipos de dados'], 422);
        }

        $userCadastrado = User::where('username', $request->username)->first();

        if ($userCadastrado) {
            return response()->json(['message' => '“Não foi possível realizar seu cadastro na Pokédex devido ao seu cadastro já existir, prossiga para o login na sua Pokédex'], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->lastname = $request->lastname;
        $user->username = $request->username;
        $user->birthdate = $request->birthdate;
        $user->city = $request->city;
        $user->save();

        return response()->json(['message' => 'Treinador, você foi registrado com sucesso na sua Pokédex'], 201);
    }

    public function signin(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => "Treinador, faltam dados para podermos autenticar você na sua Pokédex"], 422);
        }

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {

            $token = $request->user()->createToken($request->username);

            return response()->json(['token' => $token->plainTextToken], 200);
        } else {
            return response()->json(['message' => 'Treinador, parece que seus dados estão incorretos, confira e tente novamente']);
        }
    }

    public function logout(Request $request)
    {

        $user = $request->user();

        $user->tokens()->delete();

        return response()->json(['message' => 'Você saiu da sua Pokédex com sucesso'], 200);
    }

    public function dataTrainer()
    {

        $trainer = Auth::user();

        $dados = [
            'name' => $trainer->name,
            'lastname' => $trainer->lastname,
            'username' => $trainer->username,
            'city' => $trainer->city,
            'birthdate' => $trainer->birthdate
        ];

        return response()->json($dados, 200);
    }

    public function read(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if($validate->fails()){
            return response()->json(['message' => 'Treinador, estão faltando dados']);
        }

        $pokemon = Pokemon::where('id', $request->id)->first();

        if (isset($pokemon)) {
            $pokemon->id = $request->id;
            $pokemon->name = $request->name['english'];
            $pokemon->HP = $request->base['HP'];
            $pokemon->Attack = $request->base['Attack'];
            $pokemon->Defense = $request->base['Defense'];
            $pokemon->Sp_Attack = $request->base['Sp. Attack'];
            $pokemon->Sp_Defense = $request->base['Sp. Defense'];
            $pokemon->Speed = $request->base['Speed'];
            $pokemon->species = $request->species;
            $pokemon->description = $request->description;
            $pokemon->height = $request->profile['height'];
            $pokemon->weight = $request->profile['weight'];
            $pokemon->gender = $request->profile['gender'];
            $pokemon->image = $request->image['hires'];
            $pokemon->save();

            $pokemon->type()->delete();

            $types = $request->type;

            foreach ($types as $type) {
                $newType = new Type();
                $newType->type = $type;
                $newType->Pokemon()->associate($pokemon);
                $newType->save();
            }

            $pokemon->egg()->delete();

            $eggs = $request->profile['egg'];

            foreach ($eggs as $egg) {
                $newEgg = new Egg();
                $newEgg->egg = $egg;
                $newEgg->Pokemon()->associate($pokemon);
                $newEgg->save();
            }

            $pokemon->evolution()->delete();

            $evolution = $request->evolution;

            if (!empty($evolution)) {
                $Evo = new Evolution();
                if (isset($evolution['next'])) {
                    $next = $evolution['next'];
                    $intoNext = $next[0];
                    $Evo->nextStage = $intoNext[0];
                    $Evo->nextLevel = $intoNext[1];
                }
                if (isset($evolution['prev'])) {
                    $prev = $evolution['prev'];
                    $Evo->prevStage = $prev[0];
                    $Evo->prevLevel = $prev[1];
                }
                $Evo->Pokemon()->associate($pokemon);
                $Evo->save();
            }

            $pokemon->ability()->delete();

            $abilitys = $request->profile['ability'];

            foreach ($abilitys as $ability) {

                $newAbility = new Ability();
                $newAbility->name = $ability[0];

                if ($ability[1] === 'false') {
                    $hidden = false;
                } else if ($ability[1] === 'true') {
                    $hidden = true;
                }

                $newAbility->hidden = $hidden;
                $newAbility->Pokemon()->associate($pokemon);
                $newAbility->save();
            }

            return response()->json(['message' => 'Dados do Pokémon atualizados com sucesso'], 200);
        } else {

            $validate = Validator::make($request->all(), [
                'name' => 'required'
            ]);

            if($validate->fails()){
                return response()->json(['message' => 'Treinador, estão faltando dados'], 422);
            }

            $pokemonNew = new Pokemon();
            $pokemonNew->id = $request->id;
            $pokemonNew->name = $request->name['english'];
            $pokemonNew->HP = $request->base['HP'];
            $pokemonNew->Attack = $request->base['Attack'];
            $pokemonNew->Defense = $request->base['Defense'];
            $pokemonNew->Sp_Attack = $request->base['Sp. Attack'];
            $pokemonNew->Sp_Defense = $request->base['Sp. Defense'];
            $pokemonNew->Speed = $request->base['Speed'];
            $pokemonNew->species = $request->species;
            $pokemonNew->description = $request->description;
            $pokemonNew->height = $request->profile['height'];
            $pokemonNew->weight = $request->profile['weight'];
            $pokemonNew->gender = $request->profile['gender'];
            $pokemonNew->image = $request->image['hires'];
            $pokemonNew->save();

            $types = $request->type;

            foreach ($types as $type) {
                $newType = new Type();
                $newType->type = $type;
                $newType->Pokemon()->associate($pokemonNew);
                $newType->save();
            }

            $eggs = $request->profile['egg'];

            foreach ($eggs as $egg) {
                $newEgg = new Egg();
                $newEgg->egg = $egg;
                $newEgg->Pokemon()->associate($pokemonNew);
                $newEgg->save();
            }

            $evolution = $request->evolution;

            if (!empty($evolution)) {
                $Evo = new Evolution();
                if (isset($evolution['next'])) {
                    $next = $evolution['next'];
                    $intoNext = $next[0];
                    $Evo->nextStage = $intoNext[0];
                    $Evo->nextLevel = $intoNext[1];
                }
                if (isset($evolution['prev'])) {
                    $prev = $evolution['prev'];
                    $Evo->prevStage = $prev[0];
                    $Evo->prevLevel = $prev[1];
                }
                $Evo->Pokemon()->associate($pokemonNew);
                $Evo->save();
            }

            $abilitys = $request->profile['ability'];

            foreach ($abilitys as $ability) {

                $newAbility = new Ability();
                $newAbility->name = $ability[0];

                if ($ability[1] === 'false') {
                    $hidden = false;
                } else if ($ability[1] === 'true') {
                    $hidden = true;
                }

                $newAbility->hidden = $hidden;
                $newAbility->Pokemon()->associate($pokemonNew);
                $newAbility->save();
            }
            return response()->json(['message' => 'Pokémon criado com sucesso na base de dados'], 201);
        }
    }


    public function list()
    {

        $pokemons = Pokemon::all();

        $dados = [];

        foreach ($pokemons as $pokemon) {

            $dados[] = [
                'id' => $pokemon->id,
                'name' => [
                    'english' => $pokemon->name
                ],
                'image' => [
                    'hires' => $pokemon->image
                ],
            ];
        }

        return response()->json($dados, 200);
    }


    public function view(Request $request)
    {

        if(!$request->id){
            return response()->json(['message' => 'Treinador, faltou informar o número do Pokémon'], 422);
        }

        $pokemon = Pokemon::where('id', $request->id)->first();

        if(!$pokemon){
            return response()->json(['message' => 'Treinador, este Pokémon não existe na base de dados'], 404);
        }

        $dadosAbility = [];

        foreach ($pokemon->ability as $ability) {

            if ($ability->hidden === 0) {
                $hidden = 'false';
            } else if ($ability->hidden === 1) {
                $hidden = 'true';
            }

            $dadosAbility[] = [
                $ability->name,
                $hidden
            ];
        }

        foreach ($pokemon->type as $type) {

            $dadosType[] = $type->type;
        }

        foreach ($pokemon->egg as $egg) {

            $dadosEggs[] = $egg->egg;
        }

        $evolution = $pokemon->evolution;

        $dadosEvo = [];

        if ($evolution->prevStage === null) {
            if (!$evolution->nextLevel === null) {
            }

            $dadosEvo = [
                'next' => [[
                    $evolution->nextStage,
                    $evolution->nextLevel
                ]]
            ];
        } else {
            if ($evolution->nextLevel === null) {
                $dadosEvo = [
                    'prev' => [
                        $evolution->prevStage,
                        $evolution->prevLevel
                    ]
                ];
            }else{
                $dadosEvo = [
                    'prev' => [
                        $evolution->prevStage,
                        $evolution->prevLevel
                    ],
                    'next' => [[
                        $evolution->nextStage,
                        $evolution->nextLevel
                    ]]
                ];
            }
        }

        $dados = [
            'id' => $pokemon->id,
            'name' => [
                'english' => $pokemon->name
            ],
            'type' => $dadosType,
            'base' => [
                'HP' => $pokemon->HP,
                'Attack' => $pokemon->Attack,
                'Defense' => $pokemon->Defense,
                'Sp. Attack' => $pokemon->Sp_Attack,
                'Sp. Defense' => $pokemon->Sp_Defense,
                'Speed' => $pokemon->Speed,
            ],
            'species' => $pokemon->species,
            'description' => $pokemon->description,
            'evolution' => $dadosEvo,
            'profile' => [
                'height' => $pokemon->height,
                'weight' => $pokemon->weight,
                'egg' => $dadosEggs,
                'ability' => $dadosAbility,
                'gender' => $pokemon->gender
            ],
            'image' => [
                'hires' => $pokemon->image
            ]
        ];

        return response()->json($dados);
    }
}
