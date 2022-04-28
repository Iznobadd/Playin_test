<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\DepositEntryRepository;
use App\Repository\OrderEntryRepository;
use App\Repository\OrderRepository;
use App\Repository\StockEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(OrderRepository $orderRepo, OrderEntryRepository $orderEntryRepo, DepositEntryRepository $depositEntryRepo, StockEntryRepository $stockEntryRepo, EntityManagerInterface $em): Response
    {

        $validePanier = $orderRepo->findBy(['validated' => 1]);
        if(!empty($validePanier))
        {
            // SI LE PANIER EST VALIDÉ
            if($validePanier[0]->isValidated() === true)
            {
                // RECUPERER LA VALEUR DE L'ID DU PANIER
                $id_panier = $validePanier[0]->getId();
                $orderEntry = $orderEntryRepo->findBy(['order' => $id_panier]);
                // RECUPERER LA VALEUR DE L'ID DU PRODUIT
                $product_id = $orderEntry[0]->getOrder();
                // RECUPERER LA QUANTITÉ RESTANTE DU PRODUIT EN QUESTION
                $quantity = $orderEntry[0]->getQuantity();
                $depositEntry = $depositEntryRepo->findBy(['product' => $product_id]);
                $stockEntry = $stockEntryRepo->findBy(['product' => $product_id]);

                $depositEmpty = true;

                // VERIFIE SI IL Y A LES QUANTITÉS REQUISES DANS LE DEPOT
                foreach($depositEntry as $clientOrder)
                {
                    if($clientOrder->getQuantity() >= $quantity)
                    {
                        $depositEmpty = false;
                        break;
                    }
                    else
                    {
                        $depositEmpty = true;
                    }
                }

                // SI LES QUANTITÉS REQUISES SONT DANS LE DÉPOT
                if($depositEmpty === false)
                {
                    for($i = 0; $i < Count($depositEntry); $i++)
                    {
                        if($depositEntry[$i]->getQuantity() >= $quantity)
                        {
                            // UPDATE LA QUANTITE VENDUE
                            $depositEntry[$i]->setSoldQuantity($depositEntry[$i]->getSoldQuantity() + $quantity);
                            // UPDATE LA QUANTITE RESTANTE
                            $depositEntry[$i]->setQuantity($depositEntry[$i]->getQuantity() - $quantity);
                            $em->flush();
                            break;
                        }
                    }
                }

                else
                {
                    for($i = 0; $i < Count($stockEntry); $i++)
                    {

                        if($stockEntry[$i]->getQuantity() >=$quantity)
                        {
                            $stockEntry[$i]->setSoldQuantity($stockEntry[$i]->getSoldQuantity() + $quantity);
                            $stockEntry[$i]->setQuantity($stockEntry[$i]->getQuantity() - $quantity);
                            $em->flush();
                            break;
                        }
                    }
                }


            }
            else
            {
                dd('AUCUN PANIER VALIDÉ');
            }
        }

        else
        {
            dd("Aucun Panier");
        }

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
