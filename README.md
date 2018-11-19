# ToDo app
This app is the API side of a web application api/angular.
The app will manage to do tasks, sorting them by categories, and implement
an authentication system when done.

## Routes
##### Category

    /category                 -- Lister les catégories
    /category/task            -- Lister les catégories avec les taches
    
    /category/create          -- Crée une nouvelle catégorie
    /category/edit/{id}       -- Éditer la catégorie associée
    /category/delete/{id}     -- Supprimer la catégorie associée
   
##### Tasks

    /task               -- Lister les taches
    /task/todo          -- Lister les taches à faire
    /task/is-done       -- Lister les taches terminées
    
    /task/create        -- Crée une nouvelle tache
    /task/edit/{id}     -- Éditer la tache associée
    /task/delete        -- Supprimer la tache associée
    
    /task/done/{id}  -- Éditer la tache associée comme terminée
    /task/do/{id}    -- Éditer la tache associée à faire