<?php

namespace App\Services;

use App\Entity\Wish;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Upload
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function removeImage(Wish $wish): void
    {
        // Vérifiez si le wish a une image
        if ($wish->getImageName()) {
            // Chemin vers l'image
            $imagePath = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/images/wish/' . $wish->getImageName();

            // Supprimez le fichier s'il existe
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Réinitialisez la propriété imageFilename du wish
            $wish->setImageName(null);
        }
    }
}