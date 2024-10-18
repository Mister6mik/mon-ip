<?php

namespace App\Controller;

use Jenssegers\Agent\Agent;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

class DefaultController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Template('default/index.html.twig')]
    #[Route('/', name: 'app_default')]
    public function index(Request $request): array
    {
        $ipInfo = [];

        $map = null;
        $apiUrl = $this->getParameter(name: 'api.ip_url');

        $ipInfo['IP'] = '91.160.145.143'; //$request->getClientIp();
        $ipInfo['Port'] = $request->getPort();

        $response = $this->client->request('GET', $apiUrl . $ipInfo['IP']);
        $data = json_decode($response->getContent(), true);

        $ping = exec("ping -c 1 google.com", $output, $status);
        // Vérifier si la requête a réussi
        if (isset($data['status']) && $data['status'] === 'success') {
            // Extraire les informations
            $ipInfo = [
                'IP' => $data['query'],
                'City' => $data['city'],
                'ISP' => $data['isp'],
                'Country' => $data['country'],
                'Region' => $data['regionName'],
                'Timezone' => $data['timezone'],
                'Latitude' => $data['lat'],
                'Longitude' => $data['lon'],
                'Proxy' => isset($data['proxy']) && $data['proxy'] ? 'Yes' : 'No',
            ];
            $map = (new Map())
                ->center(new Point($ipInfo['Latitude'], $ipInfo['Longitude']))
                ->zoom(12)
                ->fitBoundsToMarkers();
        } else {
            // Gérer les cas où l'API renvoie une réponse invalide
            $ipInfo['error'] = 'Invalid IP information received from API';
        }
        
        $agent = new Agent();
        $other_info['device'] = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');
        $other_info['browser'] = $agent->browser();
        $other_info['platform'] = $agent->platform();

        return [
            'ip_info' => $ipInfo,
            'map' => $map,
            'other_info' => $other_info,
            'ping_status' => $status
        ];
    }
}
