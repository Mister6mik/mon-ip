<?php

namespace App\Controller;

use App\Form\SearchIpType;
use Jenssegers\Agent\Agent;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;

class DefaultController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Template('default/index.html.twig')]
    #[Route('/{ip}', name: 'app_default', requirements: ['ip' => '\d{1,3}(\.\d{1,3}){3}'], defaults: ['ip' => null])]
    public function index(?string $ip, Request $request): array|Response
    {
        $ipInfo = [];
        $map = null;
        $apiUrl = $this->getParameter(name: 'api.ip_url');

        $ipInfo['IP'] = $ip ?: $request->getClientIp(); // Real IP

        $search_form = $this->createForm(SearchIpType::class, ['IP' => $ipInfo['IP']]);
        $search_form->handleRequest($request);
        if ($search_form->isSubmitted() && $search_form->isValid()) {
            $data = $search_form->getData();
            return $this->redirectToRoute('app_default', ['ip' => $data['IP']]);
        }

        $ipInfo['Port'] = $request->getPort();

        if (filter_var($ipInfo['IP'], FILTER_VALIDATE_IP)) {

            $response = $this->client->request('GET', $apiUrl . $ipInfo['IP']);
            $data = json_decode($response->getContent(), true);

            $ping = exec("ping -c 1 google.com", $output, $status);
            $pingStatus = $status === 0 ? "OK" : "NOK";
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
        } else {
            $ipInfo['error'] = 'Invalid IP address';
        }

        $agent = new Agent();
        $other_info['device'] = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');
        $other_info['browser'] = $agent->browser();
        $other_info['platform'] = $agent->platform();

        return [
            'ip_info' => $ipInfo,
            'map' => $map,
            'other_info' => $other_info,
            'ping_status' => $pingStatus,
            'form' => $search_form
        ];
    }
}
