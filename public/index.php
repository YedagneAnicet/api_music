<?php

Header('Access-Control-Allow-Origin : *');
Header('Access-Control-Allow-Headers : *');
Header('Access-Control-Allow-Methods : GET, POST, PUT, DELETE, PATCH, OPTIONS');


require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../src/Models/Db.php';

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;


$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);

// Action sur la table admin

//Get admin 

$app->get('/api/admins/get/all', function (Request $request, Response $response, array $args) {
    $sql = "SELECT * FROM music_admins";
    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $admins = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $responseBody = json_encode($admins);
        $response->getBody()->write($responseBody);
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// get par id 

$app->get('/api/admins/get/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $sql = "SELECT * FROM music_admins WHERE idadmin = :id";
    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_OBJ);
        $db = null;
        if ($admin) {
            $responseBody = json_encode($admin);
            $response->getBody()->write($responseBody);
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $error = array(
                "message" => "Admin non trouvé"
            );
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

//creation admin 
$app->post('/api/admins/create', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();

    $usernameadminadmin = $data["usernameadminadmin"];
    $emailadminadmin = $data["emailadminadmin"];
    $teladminadmin = $data["teladminadmin"];
    $passwordadminadmin = password_hash($data["passwordadminadmin"], PASSWORD_DEFAULT);
    $dateadmin = date("DD/MM/YYYY");

    // Vérification des champs obligatoires
    if (!isset($usernameadminadmin) || !isset($emailadminadmin) || !isset($teladminadmin) || !isset($passwordadminadmin)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    $sql = "INSERT INTO music_admins(usernameadminadmin, emailadminadmin, teladminadmin, passwordadminadmin, dateadmin) VALUES (:usernameadminadmin, :emailadminadmin, :teladminadmin, :passwordadminadmin, :dateadmin)";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':usernameadminadmin', $usernameadminadmin);
        $stmt->bindParam(':emailadminadmin', $emailadminadmin);
        $stmt->bindParam(':teladminadmin', $teladminadmin);
        $stmt->bindParam(':passwordadminadmin', $passwordadminadmin);
        $stmt->bindParam(':dateadmin', $dateadmin);

        $stmt->execute();
        $lastInsertId = $conn->lastInsertId();
        $db = null;

        $responseBody = array(
            "id_admin" => $lastInsertId,
            "usernameadminadmin" => $usernameadminadmin
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(201);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// modifier un admin
$app->put('/api/admins/update/{id}', function (Request $request, Response $response, array $args) {
    // Récupérer l'ID de l'administrateur à modifier
    $id = $args['id'];

    // Récupérer les données du formulaire de modification d'administrateur
    $data = $request->getParsedBody();

    $usernameadmin = $data["usernameadmin"];
    $emailadmin = $data["emailadmin"];
    $teladmin = $data["teladmin"];
    $passwordadmin = $data["passwordadmin"];

    // Vérifier que toutes les données nécessaires ont été fournies
    if (!isset($usernameadmin) || !isset($emailadmin) || !isset($teladmin) || !isset($passwordadmin)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    // Modifier l'administrateur dans la base de données
    $sql = "UPDATE music_admins SET usernameadmin = :usernameadmin, emailadmin = :emailadmin, teladmin = :teladmin, passwordadmin = :passwordadmin WHERE idadmin = :id";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':usernameadmin', $usernameadmin);
        $stmt->bindParam(':emailadmin', $emailadmin);
        $stmt->bindParam(':teladmin', $teladmin);
        $stmt->bindParam(':passwordadmin', $passwordadmin);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
        $db = null;

        $responseBody = array(
            "message" => "Administrateur modifié avec succès"
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// supprimer un admin

$app->delete('/api/admins/delete/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "DELETE FROM music_admins WHERE Idadmin = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $db = null;

        $responseBody = array(
            "message" => "Admin supprimé avec succès"
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Action sur la table music_artistes 

// creer un artistes
$app->post('/api/artistes/create', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();

    $username = $data["username"];
    $img = $data["img"];
    $date = date("Y-m-d H:i:s");

    if (!isset($username) || !isset($img)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    $sql = "INSERT INTO music_artistes (imgartiste, usernameartiste, dateartiste) VALUES (:img, :username, :date)";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':img', $img);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':date', $date);

        $stmt->execute();
        $lastInsertId = $conn->lastInsertId();
        $db = null;

        $responseBody = array(
            "id" => $lastInsertId,
            "img" => $img,
            "username" => $username
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(201);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// modifier un artistes
$app->put('/api/music/artiste/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $data = $request->getParsedBody();

    $imgArtiste = $data["imgArtiste"];
    $usernameArtiste = $data["usernameArtiste"];

    if (!isset($imgArtiste) || !isset($usernameArtiste)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    $sql = "UPDATE music_artistes SET imgArtiste = :imgArtiste, usernameArtiste = :usernameArtiste WHERE idartiste = :id";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':imgArtiste', $imgArtiste);
        $stmt->bindParam(':usernameArtiste', $usernameArtiste);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
        $db = null;

        $responseBody = array(
            "idartiste" => $id,
            "imgArtiste" => $imgArtiste,
            "usernameArtiste" => $usernameArtiste
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// obtenir tous les artistes 
$app->get('/api/artistes/get/all', function (Request $request, Response $response, array $args) {
    $sql = "SELECT * FROM music_artistes";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;

        $response->getBody()->write(json_encode($artists));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Obtenir un artiste avec ses images et chansons
$app->get('/api/artistes/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    // Récupérer les données de l'artiste
    $db = new DB();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM music_artistes WHERE idartiste = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $artiste = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artiste) {
        $error = array(
            "message" => "Artiste introuvable"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(404);
    }

    // Récupérer les images de l'artiste
    $stmt = $conn->prepare("SELECT * FROM music_images WHERE idartiste = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les chansons de l'artiste
    $stmt = $conn->prepare("SELECT * FROM music_song WHERE idartiste = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $artiste['images'] = $images;
    $artiste['songs'] = $songs;

    $response->getBody()->write(json_encode($artiste));
    return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
});

// Suppression d'un artiste avec ses images et chansons associées
$app->delete('/api/music/artiste/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    $sql = "DELETE FROM music_images WHERE idartiste = :id;
            DELETE FROM music_song WHERE idartiste = :id;
            DELETE FROM music_artistes WHERE idartiste = :id";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':id', $id);

        $stmt->execute();
        $db = null;

        $responseBody = array(
            "idartiste" => $id
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// actions sur la table images

// Ajouter une image
$app->post('/api/images', function (Request $request, Response $response, array $args) {
    // Récupérer les données du formulaire
    $data = $request->getParsedBody();

    $idartiste = $data["id_artiste"];
    $imgPort = $data["img_port"];
    $titrePort = $data["titre_port"];

    if (!isset($idartiste) || !isset($imgPort) || !isset($titrePort)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    // Vérifier si l'artiste existe
    $db = new DB();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM music_artistes WHERE idartiste = :idartiste");
    $stmt->bindParam(':idartiste', $idartiste);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $error = array(
            "message" => "L'artiste n'existe pas"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(404);
    }

    // Enregistrement dans la base de données
    $sql = "INSERT INTO music_images(idartiste, ImgPort, TitrePort) VALUES (:idartiste, :imgPort, :titrePort)";

    try {
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':idartiste', $idartiste);
        $stmt->bindParam(':imgPort', $imgPort);
        $stmt->bindParam(':titrePort', $titrePort);

        $stmt->execute();
        $lastInsertId = $conn->lastInsertId();
        $db = null;

        $responseBody = array(
            "id_port" => $lastInsertId,
            "id_artiste" => $idartiste,
            "img_port" => $imgPort,
            "titre_port" => $titrePort
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(201);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// mettre a jour une image
$app->put('/api/images/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    // Récupérer les données du formulaire de mise à jour de l'image
    $data = $request->getParsedBody();

    $idArtiste = $data["idArtiste"];
    $imgPort = $data["imgPort"];
    $titrePort = $data["titrePort"];
    $datePort = $data["datePort"];

    if (!isset($idArtiste) || !isset($imgPort) || !isset($titrePort) || !isset($datePort)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    // Mise à jour dans la base de données
    $sql = "UPDATE music_images SET idArtiste = :idArtiste, imgPort = :imgPort, titrePort = :titrePort, datePort = :datePort WHERE IdPort = :id";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':idArtiste', $idArtiste);
        $stmt->bindParam(':imgPort', $imgPort);
        $stmt->bindParam(':titrePort', $titrePort);
        $stmt->bindParam(':datePort', $datePort);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
        $db = null;

        $responseBody = array(
            "message" => "L'image a été mise à jour avec succès"
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Récupérer une image avec l'artiste associé
$app->get('/api/images/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT mi.IdPort, mi.IdArtiste, mi.ImgPort, mi.TitrePort, mi.DatePort, ma.ImgArtiste, ma.UsernameArtiste
                FROM music_images mi
                INNER JOIN music_artistes ma ON mi.IdArtiste = ma.IdArtiste
                WHERE mi.IdPort = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $responseBody = array(
                "message" => "L'image n'a pas été trouvée"
            );

            $response->getBody()->write(json_encode($responseBody));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }

        $image = array(
            "id_port" => $result["IdPort"],
            "id_artiste" => $result["IdArtiste"],
            "img_port" => $result["ImgPort"],
            "titre_port" => $result["TitrePort"],
            "date_port" => $result["DatePort"],
            "artiste" => array(
                "img_artiste" => $result["ImgArtiste"],
                "username_artiste" => $result["UsernameArtiste"]
            )
        );

        $response->getBody()->write(json_encode($image));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);

    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Récupérer toutes les images d'un artiste

$app->get('/api/artiste/{idArtiste}/images', function (Request $request, Response $response, array $args) {
    $idArtiste = $args['idArtiste'];

    $sql = "SELECT * FROM music_images WHERE IdArtiste = :idArtiste";
    
    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idArtiste', $idArtiste);
        $stmt->execute();

        $images = array();
        while ($row = $stmt->fetch()) {
            $image = array(
                "idPort" => $row['IdPort'],
                "idArtiste" => $row['IdArtiste'],
                "imgPort" => $row['ImgPort'],
                "titrePort" => $row['TitrePort'],
                "datePort" => $row['DatePort']
            );
            array_push($images, $image);
        }

        $response->getBody()->write(json_encode($images));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);

    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// supprimer une image
$app->delete('/api/images/delete/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    try {
        // Vérifier si l'image existe dans la base de données
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT * FROM music_images WHERE IdPort = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$image) {
            $error = array(
                "message" => "L'image avec l'identifiant $id n'existe pas"
            );
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }

        // Supprimer l'image
        $stmt = $conn->prepare("DELETE FROM music_images WHERE IdPort = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Supprimer le fichier image
        unlink($image['ImgPort']);

        $db = null;

        $responseBody = array(
            "message" => "L'image a été supprimée avec succès"
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


// Create a new song for an artist
$app->post('/api/songs', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $idArtiste = $data['id_artiste'];
    $fileSong = $data['file_song'];
    $titreSong = $data['titre_song'];
    $dateSong = date('Y-m-d H:i:s');

    // Vérification des champs obligatoires
    if (!isset($idArtiste) || !isset($fileSong) || !isset($titreSong)) {
        $error = array(
            "message" => "Tous les champs obligatoires doivent être fournis"
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    $sql = "INSERT INTO music_song(id_artiste, file_song, titre_song, date_song) VALUES (:id_artiste, :file_song, :titre_song, :date_song)";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':id_artiste', $idArtiste);
        $stmt->bindParam(':file_song', $fileSong);
        $stmt->bindParam(':titre_song', $titreSong);
        $stmt->bindParam(':date_song', $dateSong);

        $stmt->execute();
        $lastInsertId = $conn->lastInsertId();
        $db = null;

        // Retrieve the artist associated with the song
        $sql = "SELECT * FROM music_artistes WHERE id_artiste = :id_artiste";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_artiste', $idArtiste);
        $stmt->execute();
        $artist = $stmt->fetch(PDO::FETCH_ASSOC);

        $responseBody = array(
            "id_song" => $lastInsertId,
            "id_artiste" => $idArtiste,
            "username_artiste" => $artist['username_artiste'],
            "file_song" => $fileSong,
            "titre_song" => $titreSong,
            "date_song" => $dateSong
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(201);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


$app->get('/api/songs', function (Request $request, Response $response, array $args) {
    $sql = "SELECT * FROM music_song";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->query($sql);
        $songs = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        $responseBody = array(
            "songs" => $songs
        );

        $response->getBody()->write(json_encode($responseBody));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


$app->run();