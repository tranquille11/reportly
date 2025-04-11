<?php

namespace Database\Seeders;

use App\Enums\TagType;
use App\Models\Disposition;
use Illuminate\Database\Seeder;

class DispositionTagSeeder extends Seeder
{
    public function run(): void
    {
        $tagMappings = [
            'Account help' => [
                'password/reset',
                'order/account help',
            ],
            'Cancel Order' => [
                'Cancel Order (auto)',
                'KNOX TOTE - Cancellation',
                "[Self-Service] Report issue cancel - I'd like to get a refund for this order",
                'simplr_cancel',
                'simplr_knox_cancel',
            ],
            'Cancel Order (NOT Preorder)' => ['Cancel Order (NOT Preorder)'],
            'Cancel PreOrder' => ['Cancel Preorder'],
            'CCPA' => ['CCPA'],
            'Chargeback' => ['Chargeback'],
            'Corporate issue' => ['Corporate issue'],
            'Escalated Matter' => ['Escalated Matter'],
            'Exchange' => [
                'Auto-response: exchange',
                'exchange',
                "Help Center] Report issue - I'd like to exchange items in my order",
                "[Self Service] Report issue - I'd like to exchange items in my order",
                "[Self-Service] Report issue - I'd like to exchange items in my order",
            ],
            'Inventory Inquiry' => ['Inventory Inquiry'],
            'Late Pick-UP' => ['Late Pick-UP'],
            'Loyalty Delayed' => ['Loyalty Delayed'],
            'Loyalty Question' => ['Loyalty Question'],
            'Modify Order' => [
                'Address change',
                "[Help Center] Report issue - I'd like to change my shipping address",
                "[Help Center] Report issue - I'd like to change the delivery date",
                "[Help Center] Report issue - I'd like to edit my order",
                'Modify Order',
                "[Self Service] Report issue - I'd like to change my shipping address",
                "[Self-Service] Report issue - I'd like to change my shipping address",
                "[Self Service] Report issue - I'd like to change the delivery date",
                "[Self-Service] Report issue - I'd like to change the delivery date",
                "[Self Service] Report issue - I'd like to edit my order",
                "[Self-Service] Report issue - I'd like to edit my order",
                'Urgent order edit',
            ],
            'Never Shipped – stores' => ['Never Shipped – stores'],
            'Never Shipped – warehouses' => ['Never Shipped – warehouses'],
            'Order Ship Late' => ['Order Ship Late'],
            'Other' => [
                'Order issue',
                'Order Issue',
                'other',
                'Other',
            ],
            'Place Order' => [
                'Place Order',
                "[Self Service] Report issue - I'd like to reorder some items",
                "[Self-Service] Report issue - I'd like to reorder some items",
            ],
            'Policy Question' => [
                'Policy Question',
                '[Self Service] What is your shipping policy?',
                'shipping policy',
            ],
            'Preorder Delay' => [
                'KNOX TOTE - Complaint',
                'Handbags Delay',
                'Preorder Delay',
                'simplr_knox_delay',
                'BSUMMIT Delay',
                'CONTAINER Delay',
            ],
            'PreOrder ETA' => ['PreOrder ETA'],
            'Product Inquiry' => [
                'Product Inquiry',
                'product & other questions',
                '[Self Service] Are your products waterproof?',
                'Interested in a product',
            ],
            'Promo Question' => [
                'Discounts/promotions',
                '[Help Center] Report issue - I forgot to apply my discount code',
                'Promo Question',
                '[Self Service] How can I get a discount?',
                '[Self-Service] Promo product & other questions',
                '[Self-Service] Report issue - I forgot to apply my discount code',
            ],
            'Recurate' => ['Recurate'],
            'Refund Inquiry' => [
                'Auto-response: refund status',
                "[Help Center] Report issue - I didn't get my refund",
                'Refund',
                'Refund Status',
                "[Self-Service] Report issue - I didn't get my refund",
                "[Help Center] Report issue - I'd like to get a refund for this order",
                "[Self Service] Report issue - I'd like to get a refund for this order",
                "[Self-Service] Report issue - I'd like to get a refund for this order",
            ],
            'Complaint about restocking fee - SMCA' => ['Complaint about restocking fee - SMCA'],
            'Retail/Wholesale Product Issues' => [
                'Retail/Wholesale Product Issues',
                'StoreCustomers',
            ],
            'Return Declined – Clearance' => ['Return Declined – Clearance'],
            'Return Declined – Too Late' => ['Return Declined – Too Late'],
            'Return Issued' => [
                'defective_item',
                '[Self Service] Report issue - The items in my order are defective',
                '[Help Center] Report issue - The items in my order are defective',
                'Return Issued',
                '[Self Service] Report issue - The items are different from what I ordered',
                '[Help Center] Report issue - The items are different from what I ordered',
            ],
            'Return label not received' => ['Return label not received'],
            'Return not processed' => ['Return not procesed'],
            'Return Question' => [
                'Auto-response: return',
                "[Help Center] Report issue - I'd like to return a product",
                "[Help Center] Report issue - I'd like to return the following items",
                'Request Return',
                'Request Return (auto)',
                'return',
                'Return help',
                'return-portal',
                'Return Question',
                "[Self Service] Report issue - I'd like to return a product",
                "[Self-Service] Report issue - I'd like to return a product",
                "[Self Service] Report issue - I'd like to return the following items",
                "[Self-Service] Report issue - I'd like to return the following items",
            ],
            'Shipment lost/damaged' => [
                '[Help Center] Report issue - My order was damaged in delivery',
                '[Help Center] Report issue - Some items are missing from my order',
                'order/damaged',
                '[Self Service] Report issue - My order was damaged in delivery',
                '[Self Service] Report issue - Some items are missing from my order',
                'Shipment lost/damaged',
                'SmartPost Issue',
            ],
            'SignupCode not received' => ['SignupCode not received'],
            'Spare Parts' => ['spare parts'],
            'Status of my order' => [
                '[Self service] Report issue - My order should have shipped by now',
                'Auto-response: order status',
                "[Help Center] Report issue - I'm past my expected delivery date",
                '[Help Center] Report issue - My order should have shipped by now',
                '[Help Center] Report issue - Where is my order?',
                'order_status',
                'Order Status',
                'order status question',
                'order status/tracking',
                "[Self service] Report issue - I'm past my expected delivery date",
                '[Self service] Report issue - Where is my order?',
                'Shipping Inquiry',
                'where-is-my-order',
                'Status of my order',
            ],
            'Unaware PREORDER' => ['Unaware PREORDER'],
            'Wholesale' => ['wholesale'],
        ];

        foreach ($tagMappings as $dispositionName => $tags) {
            $disposition = Disposition::where('name', $dispositionName)->first();
            if ($disposition) {
                $disposition->syncTagsWithType($tags, TagType::GORGIAS_REASON->value);
            }
        }
    }
}
