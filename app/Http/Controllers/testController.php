<?php

namespace App\Http\Controllers;
use Hkonnet\LaravelEbay\Facade\Ebay;
use Illuminate\Http\Request;
// Use the Composer autoloader to include the SDK.

use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;
use \Hkonnet\LaravelEbay\EbayServices;

class testController extends Controller
{
    public function show()
    {     set_time_limit(60000);
//
        //place this before any script you want to calculate time
        $time_start = microtime(true);
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();
        $request = new Types\GetMyeBaySellingRequestType();

        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $authToken = Ebay::getAuthToken();
        $request->RequesterCredentials->eBayAuthToken = $authToken;

        $request->ActiveList = new Types\ItemListCustomizationType();
        $request->ActiveList->Include = true;
        $request->ActiveList->Pagination = new Types\PaginationType();
        $request->ActiveList->Pagination->EntriesPerPage = 200; //maximum 200
        $request->ActiveList->Sort = Enums\ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;
        $pageNum = 1;
        do {
            $request->ActiveList->Pagination->PageNumber = $pageNum;
            /**
             * Send the request.
             */
            $response = $service->getMyeBaySelling($request);
            /**
             * Output the result of calling the service operation.
             */
            echo "<br>==================\nResults for page $pageNum\n==================<br>";
            if (isset($response->Errors)) {
                foreach ($response->Errors as $error) {
                    printf(
                        "%s: %s\n%s\n\n<br>",
                        $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                        $error->ShortMessage,
                        $error->LongMessage
                    );
                }
            }
            if ($response->Ack !== 'Failure' && isset($response->ActiveList)) {
                foreach ($response->ActiveList->ItemArray->Item as $item) {
                    echo "<pre>";
                    //var_dump($item);
                    //echo "</pre>";
//                    printf(
//                        "(%s) %s: %s %.2f\n<br>",
//                        $item->ItemID,
//                        $item->Title,
//                        $item->SellingStatus->CurrentPrice->currencyID,
//                        $item->SellingStatus->CurrentPrice->value
//                    );

                    echo "<span>". $item->ItemID. "</span>";
                    echo "<span>". $item->Title. "</span>";
                    echo "<span>". $item->SellingStatus->CurrentPrice->currencyID. "</span>";
                    echo "<span>". $item->SellingStatus->CurrentPrice->currencyID. "</span>";
                    echo "<img src=". $item->PictureDetails->GalleryURL."> . $item->ItemID. </img>";
                    echo "</pre>";
//                    echo "<pre>";
//                    var_dump( $item->PictureDetails->GalleryURL);
//                    echo "</pre>";
                }
            }
            $pageNum += 1;
            } while (isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);
        //}while (isset($response->ActiveList) && $pageNum <= 2);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start)/60;
        //execution time of the script
        echo '<br><b>Total Execution Time:</b> '.$execution_time.' Mins';

        return view('test')->with('content', 'San Juan Vacation');
    }
}
