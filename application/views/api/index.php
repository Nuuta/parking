<h1>Bienvenue sur l'API de gestion de parking</h1>

<p>
	<strong>Pour récupérer l'emplacement d'une voiture:</strong><br>
	GET : testParking/(id utilisateur)/(clef utilisateur)/car/(id de la voiture)
</p>
<p>
	<strong>Pour ajouter un véhicule au parking:</strong><br>
	POST : testParking/(id utilisateur)/(clef utilisateur)/car<br>
	Arguments: immatriculation_plate (string - varchar)<br>
	<br>
	La voiture se verra attribué automatiquement une place.
</p>
<p>
	<strong>Pour libérer une place de parking:</strong><br>
	DELETE : testParking/(id utilisateur)/(clef utilisateur)/car/(id de la voiture)
</p>
<p>
	<strong>Pour voir la liste des places de parking et le nombre de places restante:</strong><br>
	GET : testParking/(id utilisateur)/(clef utilisateur)/spot
</p>