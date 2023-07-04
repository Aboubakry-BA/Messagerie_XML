<?php
// Chargement du fichier XML
$xml = simplexml_load_file('messagerie.xml');

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouterUtilisateur'])) {
        $nom = $_POST['nom'];
        $numeroDeTelephone = $_POST['numeroDeTelephone'];
        $imageProfil = $_POST['imageProfil'];
        $statut = $_POST['statut'];

        $nouvelUtilisateur = ajouterUtilisateur($xml, $nom, $numeroDeTelephone, $imageProfil, $statut);

        if (isset($_POST['contacts'])) {
            $contacts = $_POST['contacts'];
            foreach ($contacts as $contact) {
                ajouterContact($nouvelUtilisateur, $contact);
            }
        }

        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['supprimerUtilisateur'])) {
        $idUtilisateur = $_POST['supprimerUtilisateur'];

        supprimerUtilisateur($xml, $idUtilisateur);
        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['modifierUtilisateur'])) {
        $idUtilisateur = $_POST['modifierUtilisateur'];
        $nouveauNom = $_POST['nouveauNom'];
        $nouveauNumeroDeTelephone = $_POST['nouveauNumeroDeTelephone'];
        $nouvelleImageProfil = $_POST['nouvelleImageProfil'];
        $nouveauStatut = $_POST['nouveauStatut'];

        modifierUtilisateur($xml, $idUtilisateur, $nouveauNom, $nouveauNumeroDeTelephone, $nouvelleImageProfil, $nouveauStatut);
        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['ajouterGroupe'])) {
        $nomGroupe = $_POST['nomGroupe'];
        $membres = $_POST['membres'];

        ajouterGroupe($xml, $nomGroupe, $membres);
        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['supprimerGroupe'])) {
        $idGroupe = $_POST['supprimerGroupe'];

        supprimerGroupe($xml, $idGroupe);
        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['modifierGroupe'])) {
        $idGroupe = $_POST['modifierGroupe'];
        $nouveauNomGroupe = $_POST['nouveauNomGroupe'];
        //$nouveauxMembres = $_POST['nouveauxMembres'];

        modifierGroupe($xml, $idGroupe, $nouveauNomGroupe/*, $nouveauxMembres*/);
        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['ajouterMessage'])) {
        $contenu = $_POST['contenu'];
        $expediteur = $_POST['expediteur'];
        $horodatage = $_POST['horodatage'];
        $destinataire = $_POST['destinataire'];
        $typeContenu = $_POST['typeContenu'];
        $statut = $_POST['statut'];
        $citation = $_POST['citation'];

        ajouterMessage($xml, $contenu, $expediteur, $horodatage, $destinataire, $typeContenu, $statut, $citation);
        $xml->asXML('messagerie.xml');
    } elseif (isset($_POST['supprimerMessage'])) {
        $idMessage = $_POST['supprimerMessage'];

        foreach ($xml->message as $message) {
            if ($message['idMessage'] == $idMessage) {
                $dom = dom_import_simplexml($message);
                $dom->parentNode->removeChild($dom);
                $xml->asXML('messagerie.xml');
                break;
            }
        }
    } elseif (isset($_POST['modifierMessage'])) {
        $idMessage = $_POST['modifierMessage'];
        $nouveauContenu = $_POST['nouveauContenu'];

        foreach ($xml->message as $message) {
            if ($message['idMessage'] == $idMessage) {
                $message->contenu = $nouveauContenu;
                $xml->asXML('messagerie.xml');
                break;
            }
        }
    }
}

// Fonction pour afficher les utilisateurs
function afficherUtilisateurs($xml)
{
    echo '<div class="utilisateurs-table">';
    echo '<h2 style="text-decoration:underline">Liste des utilisateurs</h2>';
    echo '<table>';
    echo '<tr>
          <th>Nom</th>
          <th>Numéro de téléphone</th>
          <th>Image de profil</th>
          <th>Statut</th>
          <th>Contacts</th>
          <th>Action</th>
        </tr>';

    foreach ($xml->utilisateur as $utilisateur) {
        echo '<tr>';
        echo '<td>' . $utilisateur->profil->nom . '</td>';
        echo '<td>' . $utilisateur->profil->numeroDeTelephone . '</td>';
        echo '<td>' . $utilisateur->profil->imageProfil . '</td>';
        echo '<td>' . $utilisateur->profil->statut . '</td>';

        echo '<td>';
        echo '<ul>';

        foreach ($utilisateur->contacts->contact as $contact) {
            $contactUtilisateur = $xml->xpath('//utilisateur[@idUtilisateur="' . $contact['refIdUtilisateur'] . '"]');
            echo '<li>' . $contactUtilisateur[0]->profil->nom . '</li>';
        }

        echo '</ul>';
        echo '</td>';

        echo '<td>';
        echo '<button title="Supprimer" onclick="supprimerUtilisateur(\'' . $utilisateur['idUtilisateur'] . '\')">❌</button>';
        echo '<button title="Modifier" onclick="modifierUtilisateur(\'' . $utilisateur['idUtilisateur'] . '\', \'' . $utilisateur->profil->nom . '\', \'' . $utilisateur->profil->numeroDeTelephone . '\', \'' . $utilisateur->profil->imageProfil . '\', \'' . $utilisateur->profil->statut . '\')">✍️</button>';
        echo '</td>';

        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';
}

// Fonction pour afficher les groupes
function afficherGroupes($xml)
{
    echo '<div class="groupes-table">';
    echo '<h2 style="text-decoration:underline">Liste des groupes</h2>';
    echo '<table>';
    echo '<tr>
          <th>Nom du groupe</th>
          <th>Membres</th>
          <th>Action</th>
        </tr>';

    foreach ($xml->groupe as $groupe) {
        echo '<tr>';
        echo '<td>' . $groupe->nomGroupe . '</td>';

        echo '<td>';
        echo '<ul>';

        foreach ($groupe->membres->membre as $membre) {
            $utilisateur = $xml->xpath('//utilisateur[@idUtilisateur="' . $membre['refIdUtilisateur'] . '"]');
            echo '<li>' . $utilisateur[0]->profil->nom . '</li>';
        }

        echo '</ul>';
        echo '</td>';

        echo '<td>';
        echo '<button title="Supprimer" onclick="supprimerGroupe(\'' . $groupe['idGroupe'] . '\')">❌</button>';
        echo '<button title="Modifier" onclick="modifierGroupe(\'' . $groupe['idGroupe'] . '\', \'' . $groupe->nomGroupe . '\')">✍️</button>';
        echo '</td>';

        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';
}

// Fonction pour afficher les messages
function afficherMessages($xml)
{
    echo '<div class="messages-table">';
    echo '<h2 style="text-decoration:underline">Liste des messages</h2>';
    echo '<table>';
    echo '<tr>
          <th>Expéditeur</th>
          <th>Contenu</th>
          <th>Horodatage</th>
          <th>Destinataire</th>
          <th>Type de contenu</th>
          <th>Statut</th>
          <th>Citation</th>
          <th>Action</th>
        </tr>';

    $messagesArray = [];
    foreach ($xml->message as $message) {
        $expediteur = $xml->xpath('//utilisateur[@idUtilisateur="' . $message->expediteur['refIdUtilisateur'] . '"]');

        $destinataireIdUtilisateur = $message->destinataire['refIdUtilisateur'] ?? null;
        $destinataireIdGroupe = $message->destinataire['refIdGroupe'] ?? null;

        if ($destinataireIdUtilisateur) {
            $destinataire = $xml->xpath('//utilisateur[@idUtilisateur="' . $destinataireIdUtilisateur . '"]');
        } elseif ($destinataireIdGroupe) {
            $destinataire = $xml->xpath('//groupe[@idGroupe="' . $destinataireIdGroupe . '"]/nomGroupe');
        }

        echo '<tr>';
        echo '<td>' . $expediteur[0]->profil->nom . '</td>';
        echo '<td>' . $message->contenu . '</td>';
        echo '<td>' . $message->horodatage . '</td>';
        echo '<td>' . ($destinataireIdUtilisateur ? $destinataire[0]->profil->nom : $destinataire[0]) . '</td>';
        echo '<td>' . $message['typeContenu'] . '</td>';
        echo '<td>' . $message['statut'] . '</td>';

        echo '<td>';
        echo '<ul>';

        foreach ($message->citation as $citation) {
            $messageCite = $xml->xpath('//message[@idMessage="' . $citation['refIdMessage'] . '"]/contenu');
            echo '<li>' . $messageCite[0] . '</li>';
        }

        echo '</ul>';
        echo '</td>';

        echo '<td>';
        echo '<button title="Supprimer" onclick="supprimerMessage(\'' . $message['idMessage'] . '\')">❌</button>&nbsp;&nbsp;';
        echo '<button title="Modifier" onclick="modifierMessage(\'' . $message['idMessage'] . '\', \'' . $message->contenu . '\')">✍️</button>';
        echo '</td>';

        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';

    echo '<div id="formulaire-message">';
    echo '<h2>Envoyer un nouveau message</h2>';
    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

    echo '<label for="contenu">Contenu :</label>';
    echo '<input type="text" name="contenu" id="contenu" required><br>';

    echo '<label for="expediteur">Expéditeur :</label>';
    echo '<select name="expediteur" id="expediteur" required>';
    foreach ($xml->utilisateur as $utilisateur) {
        echo '<option value="' . $utilisateur['idUtilisateur'] . '">' . $utilisateur->profil->nom . '</option>';
    }
    echo '</select><br>';

    $horodatage = date('Y-m-d\TH:i:s');
    echo '<input type="hidden" name="horodatage" value="' . $horodatage . '">';

    echo '<label for="destinataire">Destinataire :</label>';
    echo '<select name="destinataire" id="destinataire" required>';
    foreach ($xml->utilisateur as $utilisateur) {
        echo '<option value="' . $utilisateur['idUtilisateur'] . '">' . $utilisateur->profil->nom . '</option>';
    }
    foreach ($xml->groupe as $groupe) {
        echo '<option value="' . $groupe['idGroupe'] . '">' . $groupe->nomGroupe . '</option>';
    }
    echo '</select><br>';

    echo '<label for="typeContenu">Type de contenu :</label>';
    echo '<select name="typeContenu" id="typeContenu" required>';
    echo '<option value="texte">Texte</option>';
    echo '<option value="audio">Audio</option>';
    echo '<option value="fichier">Fichier</option>';
    echo '</select><br>';

    echo '<label for="statut">Statut :</label>';
    echo '<select name="statut" id="statut" required>';
    echo '<option value="envoye">Envoyé</option>';
    echo '<option value="recu">Reçu</option>';
    echo '<option value="lu">Lu</option>';
    echo '</select><br>';

    echo '<label for="citation">Citation :</label>';
    echo '<select name="citation" id="citation">';
    echo '<option value="">Aucune</option>';
    foreach ($xml->message as $message) {
        echo '<option value="' . $message['idMessage'] . '">' . $message->contenu . '</option>';
    }
    echo '</select><br>';

    echo '<button type="submit" name="ajouterMessage">Envoyer</button>';
    echo '</form>';
    echo '</div>';
}

// Fonction pour ajouter un nouvel utilisateur
function ajouterUtilisateur($xml, $nom, $numeroDeTelephone, $imageProfil, $statut)
{
    //$selected_place = $xml->xpath('/messagerie/utilisateur['.count($xml->utilisateur).']');
    $utilisateur = $xml->addChild('utilisateur');
    $utilisateur->addAttribute('idUtilisateur', 'u' . (count($xml->utilisateur) + 1));

    $profil = $utilisateur->addChild('profil');
    $profil->addChild('nom', $nom);
    $profil->addChild('numeroDeTelephone', $numeroDeTelephone);
    $profil->addChild('imageProfil', $imageProfil);
    $profil->addChild('statut', $statut);

    $utilisateur->addChild('contacts');
    //$selected_place[0]->insertAfter($utilisateur);

    return $utilisateur;
}

// Fonction pour ajouter un nouveau groupe
function ajouterGroupe($xml, $nomGroupe, $membres)
{
    $groupe = $xml->addChild('groupe');
    $groupe->addAttribute('idGroupe', 'g' . (count($xml->groupe) + 1));
    $groupe->addChild('nomGroupe', $nomGroupe);

    $membresElem = $groupe->addChild('membres');

    foreach ($membres as $membre) {
        $membreElem = $membresElem->addChild('membre');
        $membreElem->addAttribute('refIdUtilisateur', $membre);
    }

    return $groupe;
}

// Fonction pour ajouter un nouveau message
function ajouterMessage($xml, $contenu, $expediteur, $horodatage, $destinataire, $typeContenu, $statut, $citation = null)
{
    $message = $xml->addChild('message');
    $message->addAttribute('idMessage', 'm' . (count($xml->message) + 1));
    $message->addChild('contenu', $contenu);
    $message->addChild('expediteur')->addAttribute('refIdUtilisateur', $expediteur);
    $message->addChild('horodatage', $horodatage);

    if (substr($destinataire, 0, 1) === 'u') {
        $message->addChild('destinataire')->addAttribute('refIdUtilisateur', $destinataire);
    } elseif (substr($destinataire, 0, 1) === 'g') {
        $message->addChild('destinataire')->addAttribute('refIdGroupe', $destinataire);
    }

    $message->addAttribute('typeContenu', $typeContenu);
    $message->addAttribute('statut', $statut);

    if ($citation) {
        $citationElem = $message->addChild('citation');
        $citationElem->addAttribute('refIdMessage', $citation);
    }

    return $message;
}

// Fonction pour supprimer un utilisateur
function supprimerUtilisateur($xml, $idUtilisateur)
{
    foreach ($xml->utilisateur as $utilisateur) {
        if ($utilisateur['idUtilisateur'] == $idUtilisateur) {
            $dom = dom_import_simplexml($utilisateur);
            $dom->parentNode->removeChild($dom);
            break;
        }
    }
}

// Fonction pour supprimer un groupe
function supprimerGroupe($xml, $idGroupe)
{
    foreach ($xml->groupe as $groupe) {
        if ($groupe['idGroupe'] == $idGroupe) {
            $dom = dom_import_simplexml($groupe);
            $dom->parentNode->removeChild($dom);
            break;
        }
    }
}

// Fonction pour modifier les attributs d'un utilisateur
function modifierUtilisateur($xml, $idUtilisateur, $nouveauNom, $nouveauNumeroDeTelephone, $nouvelleImageProfil, $nouveauStatut)
{
    foreach ($xml->utilisateur as $utilisateur) {
        if ($utilisateur['idUtilisateur'] == $idUtilisateur) {
            $utilisateur->profil->nom = $nouveauNom;
            $utilisateur->profil->numeroDeTelephone = $nouveauNumeroDeTelephone;
            $utilisateur->profil->imageProfil = $nouvelleImageProfil;
            $utilisateur->profil->statut = $nouveauStatut;
            break;
        }
    }
}

// Fonction pour modifier les attributs d'un groupe
function modifierGroupe($xml, $idGroupe, $nouveauNomGroupe/*, $nouveauxMembres*/)
{
    foreach ($xml->groupe as $groupe) {
        if ($groupe['idGroupe'] == $idGroupe) {
            $groupe->nomGroupe = $nouveauNomGroupe;

            /*$groupe->membres = null;
            $membresElem = $groupe->addChild('membres');

            foreach ($nouveauxMembres as $membre) {
                $membreElem = $membresElem->addChild('membre');
                $membreElem->addAttribute('refIdUtilisateur', $membre);
            }*/

            break;
        }
    }
}

// Fonction pour ajouter un contact à un utilisateur
function ajouterContact($utilisateur, $contactId)
{
    $contacts = $utilisateur->contacts;
    $contactElem = $contacts->addChild('contact');
    $contactElem->addAttribute('refIdUtilisateur', $contactId);
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Service de messagerie en ligne</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 style="text-align: center;text-decoration:underline">
        Service de messagerie en ligne
    </h1>

    <?php
    afficherUtilisateurs($xml);
    ?>
    <div class="form-container">
        <h2>Ajouter un nouvel utilisateur</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required>

            <label for="numeroDeTelephone">Numéro de téléphone :</label>
            <input type="text" name="numeroDeTelephone" id="numeroDeTelephone" required>

            <label for="imageProfil">Image de profil :</label>
            <input type="text" name="imageProfil" id="imageProfil" required>

            <label for="statut">Statut :</label>
            <input type="text" name="statut" id="statut" required>

            <label for="contacts">Contacts :</label>
            <select name="contacts[]" id="contacts" multiple>
                <?php
                foreach ($xml->utilisateur as $utilisateur) {
                    echo '<option value="' . $utilisateur['idUtilisateur'] . '">' . $utilisateur->profil->nom . '</option>';
                }
                ?>
            </select>

            <button type="submit" name="ajouterUtilisateur">Ajouter</button>
        </form>
    </div>

    <?php
    afficherGroupes($xml);
    ?>

    <div class="form-container">
        <h2>Ajouter un nouveau groupe</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="nomGroupe">Nom du groupe :</label>
            <input type="text" name="nomGroupe" id="nomGroupe" required>

            <label for="membres">Membres :</label>
            <select name="membres[]" id="membres" multiple required>
                <?php
                foreach ($xml->utilisateur as $utilisateur) {
                    echo '<option value="' . $utilisateur['idUtilisateur'] . '">' . $utilisateur->profil->nom . '</option>';
                }
                ?>
            </select>

            <button type="submit" name="ajouterGroupe">Ajouter</button>
        </form>
    </div>

    <?php
    afficherMessages($xml);
    ?>

    <script>
        function supprimerUtilisateur(idUtilisateur) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                document.getElementById('supprimerUtilisateur').value = idUtilisateur;
                document.getElementById('formulaire-supprimer-utilisateur').submit();
            }
        }

        function modifierUtilisateur(idUtilisateur, nom, numeroDeTelephone, imageProfil, statut) {
            var nouveauNom = prompt('Entrez le nouveau nom pour cet utilisateur :', nom);
            var nouveauNumeroDeTelephone = prompt('Entrez le nouveau numéro de téléphone pour cet utilisateur :', numeroDeTelephone);
            var nouvelleImageProfil = prompt('Entrez le nouveau nom d\'image de profil pour cet utilisateur :', imageProfil);
            var nouveauStatut = prompt('Entrez le nouveau statut pour cet utilisateur :', statut);

            if (nouveauNom) {
                document.getElementById('modifierUtilisateur').value = idUtilisateur;
                document.getElementById('nouveauNom').value = nouveauNom;
                document.getElementById('nouveauNumeroDeTelephone').value = nouveauNumeroDeTelephone;
                document.getElementById('nouvelleImageProfil').value = nouvelleImageProfil;
                document.getElementById('nouveauStatut').value = nouveauStatut;
                document.getElementById('formulaire-modifier-utilisateur').submit();
            }
        }

        function supprimerGroupe(idGroupe) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')) {
                document.getElementById('supprimerGroupe').value = idGroupe;
                document.getElementById('formulaire-supprimer-groupe').submit();
            }
        }

        function modifierGroupe(idGroupe, nomGroupe/*, membres*/) {
            var nouveauNomGroupe = prompt('Entrez le nouveau nom pour ce groupe :', nomGroupe);
            //var nouveauxMembres = prompt('Entrez les nouveaux membres pour ce groupe (séparés par des virgules) :', membres);

            if (nouveauNomGroupe) {
                document.getElementById('modifierGroupe').value = idGroupe;
                document.getElementById('nouveauNomGroupe').value = nouveauNomGroupe;
                //document.getElementById('nouveauxMembres').value = nouveauxMembres;
                document.getElementById('formulaire-modifier-groupe').submit();
            }
        }

        function supprimerMessage(idMessage) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
                document.getElementById('supprimerMessage').value = idMessage;
                document.getElementById('formulaire-supprimer-message').submit();
            }
        }

        function modifierMessage(idMessage, contenu) {
            var nouveauContenu = prompt('Entrez le nouveau contenu pour ce message :', contenu);

            if (nouveauContenu) {
                document.getElementById('modifierMessage').value = idMessage;
                document.getElementById('nouveauContenu').value = nouveauContenu;
                document.getElementById('formulaire-modifier-message').submit();
            }
        }
    </script>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulaire-supprimer-utilisateur">
        <input type="hidden" name="supprimerUtilisateur" id="supprimerUtilisateur">
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulaire-modifier-utilisateur">
        <input type="hidden" name="modifierUtilisateur" id="modifierUtilisateur">
        <input type="hidden" name="nouveauNom" id="nouveauNom" placeholder="Nouveau nom d'utilisateur">
        <input type="hidden" name="nouveauNumeroDeTelephone" id="nouveauNumeroDeTelephone" placeholder="Nouveau numéro de téléphone">
        <input type="hidden" name="nouvelleImageProfil" id="nouvelleImageProfil" placeholder="Nouvelle image de profil">
        <input type="hidden" name="nouveauStatut" id="nouveauStatut" placeholder="Nouveau statut">
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulaire-supprimer-groupe">
        <input type="hidden" name="supprimerGroupe" id="supprimerGroupe">
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulaire-modifier-groupe">
        <input type="hidden" name="modifierGroupe" id="modifierGroupe">
        <input type="hidden" name="nouveauNomGroupe" id="nouveauNomGroupe" placeholder="Nouveau nom de groupe">
        <!-- <input type="hidden" name="nouveauxMembres" id="nouveauxMembres" placeholder="Nouveaux membres"> -->
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulaire-supprimer-message">
        <input type="hidden" name="supprimerMessage" id="supprimerMessage">
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulaire-modifier-message">
        <input type="hidden" name="modifierMessage" id="modifierMessage">
        <input type="hidden" name="nouveauContenu" id="nouveauContenu">
    </form>
</body>

</html>
