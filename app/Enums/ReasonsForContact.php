<?php

namespace App\Enums;

enum ReasonsForContact: string
{
    case AccountHelp = 'Account help';
    case CancelOrder = 'Cancel Order';
    case CancelPreOrder = 'Cancel PreOrder';
    case CCPA = 'CCPA';
    case Chargeback = 'Chargeback';
    case CorporateIssue = 'Corporate issue';
    case EscalatedMatter = 'Escalated Matter';
    case Exchange = 'Exchange';
    case InventoryInquiry = 'Inventory Inquiry';
    case LatePickUp = 'Late Pick-UP';
    case LoyaltyDelayed = 'Loyalty Delayed';
    case LoyaltyQuestion = 'Loyalty Question';
    case ModifyOrder = 'Modify order';
    case NeverShippedStores = 'Never Shipped - stores';
    case NeverShippedWarehouses = 'Never Shipped - warehouses';
    case OrderShipLate = 'Order Ship Late';
    case Other = 'Other';
    case PlaceOrder = 'Place Order';
    case PolicyQuestion = 'Policy Question';
    case PreOrderDelay = 'PreOrder Delay';
    case PreOrderETA = 'PreOrder ETA';
    case ProductInquiry = 'Product Inquiry';
    case PromoQuestion = 'Promo Question';
    case Recurate = 'Recurate';
    case RefundInquiry = 'Refund Inquiry';
    case RestockingFeeSMCA = 'Restocking fee Complaint SMCA';
    case RetailWholesaleProductIssues = 'Retail/Wholesale Product Issues';
    case ReturnDeclinedClearance = 'Return Declined – Clearance';
    case ReturnDeclinedTooLate = 'Return Declined – Too Late';
    case ReturnIssued = 'Return Issued';
    case ReturnLabelNotReceived = 'Return label not received';
    case ReturnNotProcessed = 'Return not processed';
    case ReturnQuestion = 'Return Question';
    case ShipmentLostDamaged = 'Shipment lost/damaged';
    case SignUpCodeNotReceived = 'SignupCode not received';
    case SpareParts = 'Spare Parts';
    case StatusOfMyOrder = 'Status of my order';
    case UnawarePreOrder = 'Unaware PREORDER';
    case Wholesale = 'Wholesale';

    public static function get($tag): ReasonsForContact
    {
        return match ($tag) {
            'Cancel Preorder' => self::CancelPreOrder,
            'CCPA' => self::CCPA,
            'Chargeback' => self::Chargeback,
            'Corporate issue' => self::CorporateIssue,
            'Escalated Matter' => self::EscalatedMatter,
            'Inventory Inquiry' => self::InventoryInquiry,
            'Late Pick-UP' => self::LatePickUp,
            'Loyalty Delayed' => self::LoyaltyDelayed,
            'Loyalty Question' => self::LoyaltyQuestion,
            'Never Shipped – stores' => self::NeverShippedStores,
            'Never Shipped – warehouses' => self::NeverShippedWarehouses,
            'Order Ship Late' => self::OrderShipLate,
            'PreOrder ETA' => self::PreOrderETA,
            'Recurate' => self::Recurate,
            'Return Declined – Clearance' => self::ReturnDeclinedClearance,
            'Return Declined – Too Late' => self::ReturnDeclinedTooLate,
            'Complaint about restocking fee - SMCA' => self::RestockingFeeSMCA,
            'Return label not received' => self::ReturnLabelNotReceived,
            'Return not procesed' => self::ReturnNotProcessed,
            'SignupCode not received' => self::SignUpCodeNotReceived,
            'Unaware PREORDER' => self::UnawarePreOrder,
            'wholesale' => self::Wholesale,
            'spare parts' => self::SpareParts,

            'Retail/Wholesale Product Issues',
            'StoreCustomers' => self::RetailWholesaleProductIssues,

            'password/reset',
            'order/account help' => self::AccountHelp,

            'Policy Question',
            '[Self Service] What is your shipping policy?',
            'shipping policy' => self::PolicyQuestion,

            'Place Order',
            '[Self Service] Report issue - I’d like to reorder some items',
            '[Self-Service] Report issue - I’d like to reorder some items', => self::PlaceOrder,

            'Order issue',
            'Order Issue',
            'other',
            'Other' => self::Other,

            'Auto-response: refund status',
            "[Help Center] Report issue - I didn't get my refund",
            'Refund',
            'Refund Status',
            "[Self-Service] Report issue - I didn't get my refund",
            "[Help Center] Report issue - I'd like to get a refund for this order",
            "[Self Service] Report issue - I'd like to get a refund for this order",
            "[Self-Service] Report issue - I'd like to get a refund for this order" => self::RefundInquiry,

            'Product Inquiry',
            'product & other questions',
            '[Self Service] Are your products waterproof?',
            'Interested in a product' => self::ProductInquiry,

            'Discounts/promotions',
            '[Help Center] Report issue - I forgot to apply my discount code',
            'Promo Question',
            '[Self Service] How can I get a discount?',
            '[Self-Service] Promo product & other questions',
            '[Self-Service] Report issue - I forgot to apply my discount code' => self::PromoQuestion,

            'KNOX TOTE - Complaint',
            'Handbags Delay',
            'Preorder Delay',
            'simplr_knox_delay',
            'BSUMMIT Delay',
            'CONTAINER Delay' => self::PreOrderDelay,

            'Auto-response: exchange',
            'exchange',
            "[Help Center] Report issue - I'd like to exchange items in my order",
            "[Self Service] Report issue - I'd like to exchange items in my order",
            "[Self-Service] Report issue - I'd like to exchange items in my order" => self::Exchange,

            'defective_item',
            '[Self Service] Report issue - The items in my order are defective',
            '[Help Center] Report issue - The items in my order are defective',
            'Return Issued',
            '[Self Service] Report issue - The items are different from what I ordered',
            '[Help Center] Report issue - The items are different from what I ordered', => self::ReturnIssued,

            'Cancel Order (auto)',
            'Cancel Order (NOT Preorder)',
            'KNOX TOTE - Cancellation',
            "[Self-Service] Report issue cancel - I'd like to get a refund for this order",
            'simplr_cancel',
            'simplr_knox_cancel' => self::CancelOrder,

            '[Help Center] Report issue - My order was damaged in delivery',
            '[Help Center] Report issue - Some items are missing from my order',
            'order/damaged',
            '[Self Service] Report issue - My order was damaged in delivery',
            '[Self Service] Report issue - Some items are missing from my order',
            'Shipment lost/damaged',
            'SmartPost Issue' => self::ShipmentLostDamaged,

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
            'Urgent order edit', => self::ModifyOrder,

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
            "[Self-Service] Report issue - I'd like to return the following items" => self::ReturnQuestion,

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
            'Status of my order', => self::StatusOfMyOrder,
        };
    }
}
