{% extends 'base.html.twig' %}

{% block title %}Page non trouvée !{% endblock %}

{% block body %}
<h1>Page non trouvée</h1>

<p>
    La page demandée n'a pu être trouvée <a href="{{ path('home') }}">retour à l'accueil</a>.
</p>
{% endblock %}