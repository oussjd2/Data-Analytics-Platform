<?php

namespace App\Controller;

use App\Repository\LeadsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LeadsController extends AbstractController
{
    private LeadsRepository $leadsRepository;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private string $openAiApiKey;

    public function __construct(
        LeadsRepository $leadsRepository,
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        string $openAiApiKey
    ) {
        $this->leadsRepository = $leadsRepository;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->openAiApiKey = $openAiApiKey;
    }

    private function checkAccess()
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_USER')) {
            throw new AccessDeniedException('This action requires higher authority.');
        }
    }

    #[Route('/leads', name: 'leads_index')]
    public function index(): Response
    {
        $this->checkAccess();
        // Fetch all leads and display them in a table
        $leads = $this->leadsRepository->findAll();

        return $this->render('leads/index.html.twig', [
            'leads' => $leads,
        ]);
    }

    #[Route('/leads/dashboard', name: 'leads_dashboard')]
    public function dashboard(): Response
    {
        $this->checkAccess();
        // Fetch all leads and prepare chart data
        $leads = $this->leadsRepository->findAll();
        $chartData = $this->prepareChartData($leads);

        // Render the dashboard with chart data and configurations
        return $this->render('leads/dashboard.html.twig', [
            'chartData' => $chartData,
            'chartConfigs' => [
                // Define chart configurations for each chart type
                'sourceDistribution' => ['title' => 'Lead Source Distribution', 'type' => 'pie'],
                'monthlyLeads' => ['title' => 'Monthly Lead Generation', 'type' => 'line'],
                'porteurPerformance' => ['title' => 'Top Performing Porteurs', 'type' => 'bar'],
                'rtcPreference' => ['title' => 'RTC Preference', 'type' => 'doughnut'],
                'conversionByPartner' => ['title' => 'Conversion Rate by Partner', 'type' => 'bar'],
                'phoneCountryCode' => ['title' => 'Lead Distribution by Phone Country Code', 'type' => 'bar'],
                'conversionTimeline' => ['title' => 'Lead Conversion Timeline', 'type' => 'line'],
                'campaignPerformance' => ['title' => 'Top Performing Campaigns', 'type' => 'bar']
            ],
        ]);
    }

    #[Route('/leads/dashboard/data', name: 'leads_dashboard_data')]
    public function dashboardData(Request $request): JsonResponse
    {
        $this->checkAccess();
        try {
            // Parse start and end dates from the request
            $startDate = new \DateTime($request->query->get('startDate'));
            $endDate = new \DateTime($request->query->get('endDate'));

            // Fetch leads within the specified date range
            $leads = $this->leadsRepository->findByDateRange($startDate, $endDate);
            
            if (empty($leads)) {
                // Log a warning if no leads are found
                $this->logger->warning('No leads found for date range: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));
            }
            
            // Prepare chart data for the fetched leads
            $chartData = $this->prepareChartData($leads);

            // Return the chart data as JSON
            return $this->json(['chartData' => $chartData]);
        } catch (\Exception $e) {
            // Log any errors that occur during data fetching or processing
            $this->logger->error('Error in dashboardData: ' . $e->getMessage(), [
                'exception' => $e,
                'startDate' => $request->query->get('startDate'),
                'endDate' => $request->query->get('endDate')
            ]);
            
            // Return an error response
            return $this->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/leads/analyze-chart', name: 'analyze_chart', methods: ['POST'])]
    public function analyzeChart(Request $request): JsonResponse
    {
        $this->checkAccess();
        try {
            // Log the incoming request for debugging
            $this->logger->info('Received analyze chart request', [
                'request' => $request->request->all(),
                'content' => $request->getContent()
            ]);

            // Extract data from the request
            $chartId = $request->request->get('chartId');
            $chartData = json_decode($request->request->get('chartData'), true);
            $dateRange = $request->request->get('dateRange');
            $context = $request->request->get('context'); // New: Get additional context

            // Validate required parameters
            if (!$chartId || !$chartData || !$dateRange) {
                throw new \InvalidArgumentException('Missing required parameters');
            }

            // Log the extracted data for debugging
            $this->logger->info('Analyze chart request', [
                'chartId' => $chartId,
                'chartData' => $chartData,
                'dateRange' => $dateRange,
                'context' => $context
            ]);

            // Prepare the prompt for GPT
            $prompt = $this->prepareGptPrompt($chartId, $chartData, $dateRange, $context);

            // Log the prepared prompt
            $this->logger->info('Sending request to OpenAI', [
                'prompt' => $prompt
            ]);

            // Make the API call to OpenAI
            if (empty($this->openAiApiKey)) {
                throw new \Exception('OpenAI API key is not set.');
            }

            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional business analyst providing insights on chart data.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new \Exception('OpenAI API returned non-200 status code: ' . $statusCode);
            }

            $content = $response->toArray();
            $this->logger->info('Received response from OpenAI', [
                'response' => $content
            ]);

            $analysis = $content['choices'][0]['message']['content'];

            // Return the analysis as JSON
            return $this->json(['analysis' => $analysis]);
        } catch (\Exception $e) {
            // Log any errors that occur
            $this->logger->error('Error in chart analysis: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse(['error' => 'An error occurred during analysis: ' . $e->getMessage()], 500);
        }
    }

    private function prepareGptPrompt(string $chartId, array $chartData, string $dateRange, ?string $context): string
    {
        $chartDescriptions = [
            'sourceDistribution' => 'distribution of lead sources',
            'monthlyLeads' => 'monthly lead generation',
            'porteurPerformance' => 'top performing porteurs',
            'rtcPreference' => 'RTC (Real-Time Communication) preference',
            'conversionByPartner' => 'conversion rates by partner',
            'phoneCountryCode' => 'lead distribution by country',
            'conversionTimeline' => 'lead conversion rate over time',
            'campaignPerformance' => 'top-performing campaigns based on conversion rates',
        ];

        $description = $chartDescriptions[$chartId] ?? 'chart data';

        // Construct a more detailed prompt
        $prompt = "As a professional business analyst, provide a comprehensive analysis of the following $description for the period $dateRange. ";
        $prompt .= "Include the following in your analysis:\n";
        $prompt .= "• Key Observations: Highlight the most significant patterns or trends in the data.\n";
        $prompt .= "• Data Interpretation: Explain what these trends might indicate about the business performance.\n";
        $prompt .= "• Actionable Insights: Suggest specific actions or strategies based on the data.\n";
        $prompt .= "• Potential Risks or Opportunities: Identify any potential risks or opportunities revealed by the data.\n";
        $prompt .= "• Recommendations: Provide clear, actionable recommendations for improvement or optimization.\n\n";
        
        if ($context) {
            $prompt .= "Additional Context: $context\n\n";
        }

        $prompt .= "Data: " . json_encode($chartData);

        return $prompt;
    }

    private function prepareChartData(array $leads): array
    {
        // Initialize data structures for each chart
        $sourceDistribution = [];
        $monthlyLeads = [];
        $porteurPerformance = [];
        $rtcPreference = ['yes' => 0, 'no' => 0];
        $conversionByPartner = [];
        $phoneCountryCode = [];
        $conversionTimeline = [];
        $campaignPerformance = [];

        foreach ($leads as $lead) {
            // Process each lead and update the respective data structures

            // Source Distribution
            $source = $lead->getSource() ?: 'Unknown';
            $sourceDistribution[$source] = ($sourceDistribution[$source] ?? 0) + 1;

            // Monthly Leads
            $month = $lead->getCreatedAt()->format('Y-m');
            $monthlyLeads[$month] = ($monthlyLeads[$month] ?? 0) + 1;

            // Porteur Performance
            $porteur = $lead->getPorteur() ?: 'Unknown';
            $porteurPerformance[$porteur] = ($porteurPerformance[$porteur] ?? 0) + 1;

            // RTC Preference
            $rtc = $lead->getRtc() ?: 'Unknown';
            $rtcPreference[$rtc] = ($rtcPreference[$rtc] ?? 0) + 1;

            // Conversion by Partner
            $partner = $lead->getPartner() ?: 'Unknown';
            if (!isset($conversionByPartner[$partner])) {
                $conversionByPartner[$partner] = ['total' => 0, 'converted' => 0];
            }
            $conversionByPartner[$partner]['total']++;
            if ($lead->getExported()) {
                $conversionByPartner[$partner]['converted']++;
            }

            // Phone Country Code Distribution
            $phone = $lead->getPhone();
            $countryCode = $this->extractCountryCode($phone);
            $phoneCountryCode[$countryCode] = ($phoneCountryCode[$countryCode] ?? 0) + 1;

            // Conversion Timeline
            $date = $lead->getCreatedAt()->format('Y-m-d');
            if (!isset($conversionTimeline[$date])) {
                $conversionTimeline[$date] = ['total' => 0, 'converted' => 0];
            }
            $conversionTimeline[$date]['total']++;
            if ($lead->getExported()) {
                $conversionTimeline[$date]['converted']++;
            }

            // Campaign Performance
            $campaign = $lead->getCampaign() ?: 'Unknown';
            if (!isset($campaignPerformance[$campaign])) {
                $campaignPerformance[$campaign] = ['total' => 0, 'converted' => 0];
            }
            $campaignPerformance[$campaign]['total']++;
            if ($lead->getExported()) {
                $campaignPerformance[$campaign]['converted']++;
            }
        }

        // Post-processing of data

        // Calculate conversion rates for partners
        foreach ($conversionByPartner as &$data) {
            $data['rate'] = $data['total'] > 0 ? round(($data['converted'] / $data['total']) * 100, 2) : 0;
        }

        // Sort and slice data for better visualization
        arsort($sourceDistribution);
        ksort($monthlyLeads);
        arsort($porteurPerformance);
        arsort($phoneCountryCode);
        uasort($conversionByPartner, fn($a, $b) => $b['rate'] <=> $a['rate']);

        // Sort the conversion timeline by date
        ksort($conversionTimeline);

        // Calculate conversion rates for the timeline
        foreach ($conversionTimeline as &$data) {
            $data['rate'] = $data['total'] > 0 ? round(($data['converted'] / $data['total']) * 100, 2) : 0;
        }

        // Helper function to ensure data is not empty
        $ensureData = fn($data) => empty($data) ? ['No Data' => 1] : $data;

        // Prepare and return the final chart data
        return [
            'sourceDistribution' => $ensureData(array_slice($sourceDistribution, 0, 5)),
            'monthlyLeads' => $ensureData($monthlyLeads),
            'porteurPerformance' => $ensureData(array_slice($porteurPerformance, 0, 5)),
            'rtcPreference' => $ensureData($rtcPreference),
            'conversionByPartner' => $ensureData(array_slice($conversionByPartner, 0, 5)),
            'phoneCountryCode' => $ensureData(array_slice($phoneCountryCode, 0, 5)),
            'conversionTimeline' => $conversionTimeline,
            'campaignPerformance' => array_slice($campaignPerformance, 0, 5), // Top 5 campaigns
        ];
    }

    private function extractCountryCode(string $phone): string
    {
        // Extract country code from phone number
        if (preg_match('/^(\+|00)(\d{1,3})/', $phone, $matches)) {
            return '+' . $matches[2];
        }
        return 'Unknown';
    }

    #[Route('/leads/custom-chart', name: 'leads_custom_chart')]
    public function customChart(): Response
    {
        $this->checkAccess();
        $availableFields = [
            'source' => 'Lead Source',
            'createdAt' => 'Creation Date',
            'porteur' => 'Porteur',
            'rtc' => 'RTC',
            'partner' => 'Partner',
            'phone' => 'Phone',
            'campaign' => 'Campaign',
            'exported' => 'Exported Status'
        ];

        $chartTypes = ['bar', 'line', 'pie', 'doughnut'];

        return $this->render('leads/custom_chart.html.twig', [
            'availableFields' => $availableFields,
            'chartTypes' => $chartTypes,
        ]);
    }

    #[Route('/leads/generate-custom-chart', name: 'generate_custom_chart', methods: ['POST'])]
    public function generateCustomChart(Request $request): JsonResponse
    {
        $this->checkAccess();
        try {
            $xAxis = $request->request->get('xAxis');
            $yAxis = $request->request->get('yAxis');
            $chartType = $request->request->get('chartType');
            $startDate = new \DateTime($request->request->get('startDate'));
            $endDate = new \DateTime($request->request->get('endDate'));

            $leads = $this->leadsRepository->findByDateRange($startDate, $endDate);

            $chartData = $this->prepareCustomChartData($leads, $xAxis, $yAxis);

            return $this->json([
                'labels' => array_keys($chartData),
                'data' => array_values($chartData),
                'chartType' => $chartType,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error generating custom chart: ' . $e->getMessage());
            return $this->json(['error' => 'An error occurred while generating the chart.'], 500);
        }
    }

    private function prepareCustomChartData(array $leads, string $xAxis, string $yAxis): array
    {
        $data = [];

        foreach ($leads as $lead) {
            $xValue = $this->getLeadFieldValue($lead, $xAxis);
            $yValue = $this->getLeadFieldValue($lead, $yAxis);

            if (!isset($data[$xValue])) {
                $data[$xValue] = 0;
            }

            if ($yAxis === 'count') {
                $data[$xValue]++;
            } else {
                $data[$xValue] += (float)$yValue;
            }
        }

        return $data;
    }

    private function getLeadFieldValue($lead, string $field)
    {
        switch ($field) {
            case 'source':
                return $lead->getSource() ?: 'Unknown';
            case 'createdAt':
                return $lead->getCreatedAt()->format('Y-m-d');
            case 'porteur':
                return $lead->getPorteur() ?: 'Unknown';
            case 'rtc':
                return $lead->getRtc() ? 'Yes' : 'No';
            case 'partner':
                return $lead->getPartner() ?: 'Unknown';
            case 'phone':
                return $this->extractCountryCode($lead->getPhone());
            case 'campaign':
                return $lead->getCampaign() ?: 'Unknown';
            case 'exported':
                return $lead->getExported() ? 'Yes' : 'No';
            case 'count':
                return 1;
            default:
                return 'Unknown';
        }
    }

    #[Route('/leads/cross-chart-analysis', name: 'cross_chart_analysis', methods: ['POST'])]
    public function crossChartAnalysis(Request $request): JsonResponse
    {
        $this->checkAccess();
        try {
            $chart1 = $request->request->get('chart1');
            $chart2 = $request->request->get('chart2');
            $chartData1 = json_decode($request->request->get('chartData1'), true);
            $chartData2 = json_decode($request->request->get('chartData2'), true);
            $dateRange = $request->request->get('dateRange');
            $context = $request->request->get('context');

            // Prepare the prompt for GPT
            $prompt = $this->prepareCrossChartPrompt($chart1, $chart2, $chartData1, $chartData2, $dateRange, $context);

            // Make the API call to OpenAI
            if (empty($this->openAiApiKey)) {
                throw new \Exception('OpenAI API key is not set.');
            }

            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional business analyst providing insights on cross-chart data.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new \Exception('OpenAI API returned non-200 status code: ' . $statusCode);
            }

            $content = $response->toArray();
            $analysis = $content['choices'][0]['message']['content'];

            return $this->json(['analysis' => $analysis]);
        } catch (\Exception $e) {
            $this->logger->error('Error in cross-chart analysis: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse(['error' => 'An error occurred during analysis: ' . $e->getMessage()], 500);
        }
    }

    private function prepareCrossChartPrompt(string $chart1, string $chart2, array $chartData1, array $chartData2, string $dateRange, ?string $context): string
    {
        $chartDescriptions = [
            'sourceDistribution' => 'distribution of lead sources',
            'monthlyLeads' => 'monthly lead generation',
            'porteurPerformance' => 'top performing porteurs',
            'rtcPreference' => 'RTC (Real-Time Communication) preference',
            'conversionByPartner' => 'conversion rates by partner',
            'phoneCountryCode' => 'lead distribution by country',
            'conversionTimeline' => 'lead conversion rate over time',
            'campaignPerformance' => 'top-performing campaigns based on conversion rates',
        ];

        $description1 = $chartDescriptions[$chart1] ?? 'first chart data';
        $description2 = $chartDescriptions[$chart2] ?? 'second chart data';

        $prompt = "As a professional business analyst, provide a comprehensive cross-chart analysis comparing the $description1 and the $description2 for the period $dateRange. ";
        $prompt .= "Include the following in your analysis:\n";
        $prompt .= "• Key Observations: Highlight the most significant patterns or trends when comparing both datasets.\n";
        $prompt .= "• Data Interpretation: Explain what these trends might indicate about the business performance and how they relate to each other.\n";
        $prompt .= "• Correlations and Insights: Identify any correlations or interesting relationships between the two datasets.\n";
        $prompt .= "• Actionable Insights: Suggest specific actions or strategies based on the combined analysis of both charts.\n";
        $prompt .= "• Potential Risks or Opportunities: Identify any potential risks or opportunities revealed by comparing the two datasets.\n";
        $prompt .= "• Recommendations: Provide clear, actionable recommendations for improvement or optimization based on the cross-chart analysis.\n\n";
        
        $prompt .= "Data for $chart1: " . json_encode($chartData1) . "\n\n";
        $prompt .= "Data for $chart2: " . json_encode($chartData2) . "\n\n";

        if ($context) {
            $prompt .= "Additional Context: $context\n\n";
        }

        $prompt .= "Please provide your analysis based on the above data and context.";

        return $prompt;
    }
}
