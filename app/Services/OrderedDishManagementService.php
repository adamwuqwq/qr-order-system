<?php

namespace App\Services;

use App\Models\OrderedDishes;
use App\Models\Orders;
use App\Models\Dishes;

class OrderedDishManagementService
{
    /**
     * 注文済み料理の一覧取得(by 注文ID)
     * @param string $orderID 注文ID
     * @param bool $isCanceled キャンセル済みの料理も取得するかどうか
     * @return array 注文済み料理一覧
     */
    public static function getOrderedDishListByOrder(string $orderID, ?bool $isCanceled = false)
    {
        $orderedDishes = OrderedDishes::where('order_id', $orderID)->get();

        // キャンセル済みの料理を除外
        if (!$isCanceled) {
            $orderedDishes = $orderedDishes->filter(function ($dish) {
                return !$dish->is_canceled;
            });
        }

        // unsetを使って、カラム'order_id'を削除
        foreach ($orderedDishes as $dish) {
            unset($dish['order_id']);
        }

        return $orderedDishes;
    }

    /**
     * 注文済み料理の一覧取得(by 店舗ID)
     * @param string $restaurantID 店舗ID
     * @param bool $isCanceled キャンセル済みの料理も取得するかどうか
     * @return array 注文済み料理一覧
     */
    public static function getOrderedDishListByRestaurant(string $restaurantID, ?bool $isCanceled = false)
    {
        $orderedDishes = OrderedDishes::where('restaurant_id', $restaurantID)->get();

        // キャンセル済みの料理を除外
        if (!$isCanceled) {
            $orderedDishes = $orderedDishes->filter(function ($dish) {
                return !$dish->is_canceled;
            });
        }

        // unsetを使って、カラム'restaurant_id'を削除
        foreach ($orderedDishes as $dish) {
            unset($dish['restaurant_id']);
        }

        return $orderedDishes;
    }

    /**
     * 注文済み料理の総額取得
     * @param string $orderID 注文ID
     * @return int|null 注文済み料理の総額
     */
    public static function getTotalPrice(string $orderID)
    {
        $orderedDishes = OrderedDishes::where('order_id', $orderID)->get();

        // 注文済み料理の総額を計算
        $totalPrice = 0;
        foreach ($orderedDishes as $dish) {
            // キャンセルされていない料理のみ計算
            if (!$dish->is_canceled) {
                $totalPrice += ($dish->dish()->price * $dish->quantity);
            }
        }

        return $orderedDishes === null ? null : $totalPrice;
    }

    /**
     * 料理の新規注文作成
     * @param Orders $order 注文情報
     * @param string $dishID 料理ID
     * @param int $quantity 注文数
     * @return string 作成された注文済み料理のID
     * @throws \Exception 作成に失敗した
     */
    public static function createOrderedDish(Orders $order, string $dishID, int $quantity)
    {
        // エラー処理
        if (Dishes::find($dishID) === null) {
            throw new \Exception('料理IDが不正です');
        }
        if ($quantity <= 0 || $quantity > Dishes::find($dishID)->available_num) {
            throw new \Exception('注文数量が不正です');
        }

        // 注文済み料理の新規作成
        $orderedDish = new OrderedDishes();
        $orderedDish->order_id = $order->order_id;
        $orderedDish->restaurant_id = $order->restaurant_id;
        $orderedDish->dish_id = $dishID;
        $orderedDish->quantity = $quantity;
        $orderedDish->is_delivered = false;
        $orderedDish->is_canceled = false;

        $orderedDish->save();

        return $orderedDish->id;
    }

    /**
     * 注文済み料理の詳細情報取得
     * @param string $orderedDishID 注文済み料理ID
     * @return OrderedDishes 注文済み料理情報
     */
    public static function getOrderedDishInfo(string $orderedDishID)
    {
        return OrderedDishes::find($orderedDishID);
    }

    /**
     * 注文済み料理のキャンセル
     * @param string $orderedDishID 注文済み料理ID
     * @return void
     * @throws \Exception キャンセルに失敗した
     */
    public static function cancelOrderedDish(string $orderedDishID)
    {
        $orderedDish = OrderedDishes::find($orderedDishID);

        // エラー処理
        if ($orderedDish === null) {
            throw new \Exception('注文済み料理IDが不正です');
        }
        if ($orderedDish->is_delivered) {
            throw new \Exception('既に配達済みの料理はキャンセルできません');
        }

        $orderedDish->is_canceled = true;
        $orderedDish->save();
    }

    /**
     * 注文済み料理の配達完了
     * @param string $orderedDishID 注文済み料理ID
     * @return void
     * @throws \Exception 配達完了に失敗した
     */
    public static function deliverOrderedDish(string $orderedDishID)
    {
        $orderedDish = OrderedDishes::find($orderedDishID);

        // エラー処理
        if ($orderedDish === null) {
            throw new \Exception('注文済み料理IDが不正です');
        }

        $orderedDish->is_delivered = true;
        $orderedDish->save();
    }
}