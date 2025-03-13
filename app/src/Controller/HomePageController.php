<?php

namespace App\Controller;


use App\Service\Uploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class HomePageController extends AbstractController
{
    public function __construct(
        private readonly Uploader $uploader,
    ) {}

    #[Route("/", name: "app_home_page")]
    public function index(Request $request): Response
    {
        return $this->render("home_page/index.html.twig", [
            "controller_name" => "HomePageController",
        ]);
    }

    #[Route("/csv/upload", name: "csv_upload", methods: ["POST"])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get("chunk");
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(["error" => "Invalid file"], Response::HTTP_BAD_REQUEST);
        }
        $fileName = $request->request->get("fileName");
        if (!is_string($fileName) || empty($fileName)) {
            return new JsonResponse(["error" => "Invalid file name"], Response::HTTP_BAD_REQUEST);
        }
        $chunkIndex = $request->request->get("chunkIndex");
        if(!is_numeric($chunkIndex)) {
            return new JsonResponse(["error" => "Nieprawidłowy numer fragmentu"], Response::HTTP_BAD_REQUEST);
        }
        $totalChunks = $request->request->get("totalChunks");
        if(!is_numeric($totalChunks)) {
            return new JsonResponse(["error" => "Nieprawidłowy numer fragmentu"], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->uploader->saveChunk(
            $file,
            $fileName,
            (int)$chunkIndex,
            (int)$totalChunks
        );

        if ($result["status"] === "completed") {

            return new JsonResponse(
                [
                    "message" => "file merged",
                    "file" => $result["file"],
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            ["message" => "Chunk saved"],
            Response::HTTP_OK
        );
    }

    #[Route('/users', name: 'user_list')]
    public function listUsers(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $perPage = 10;

        //TODO ADD USER list
        // $pagination = $this->userStorage->getAllUsersPaginated($page, $perPage);

        return $this->render('user/list.html.twig', [
            'users' => $pagination['users'] ?? [],
            'totalPages' => $pagination['totalPages'] ?? '',
            'currentPage' => $pagination['currentPage'] ?? '',
        ]);
    }

    #[Route('/report/latest', name: 'latest_report')]
    public function latestReport(): Response
    {
        try {
            $report = 1; //TODO GET LAST REPORT
            $decodedErrors = [];

//            foreach ($report->getErrors() as $errorGroup) {
//                if (is_string($errorGroup)) {
//                    $errorGroup = json_decode($errorGroup, true);
//                }
//
//                if (is_array($errorGroup)) {
//                    foreach ($errorGroup as $error) {
//                        $decodedErrors[] = $error;
//                    }
//                }
//            }
        } catch (\Exception $e) {
            return $this->render('report/latest.html.twig', [
                'error' => $e->getMessage(),
                'report' => null,
            ]);
        }
//        list($startMicro, $startSec) = explode(" ", $report->getStartTime());
//        list($endMicro, $endSec) = explode(" ", $report->getEndTime());
//        $startTime = (float)$startSec + (float)$startMicro;
//        $endTime = (float)$endSec + (float)$endMicro;
//        $elapsedTime = $endTime - $startTime;

        return $this->render('report/latest.html.twig', [
            'report' => $report,
            'error' => null,
            'elapsedTime' => $elapsedTime ?? '',
            'raportErrors' => $decodedErrors
        ]);
    }

}
