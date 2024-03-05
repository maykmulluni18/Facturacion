<?php

    namespace App\Http\Resources\Tenant;

    use App\Models\Tenant\Configuration;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    /**
     * Class SaleNoteCollection
     *
     * @package App\Http\Resources\Tenant
     */
    class SmallBoxCollection extends ResourceCollection {
        /**
         * Transform the resource collection into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array|\Illuminate\Support\Collection
         */
        public function toArray($request) {
            $configuration = Configuration::first();
            return $this->collection->transform(function ($row, $key) use($configuration) {
                /** @var \App\Models\Tenant\SmallBox $row */
                return [
                    'id'                           => $row->id,
                    'description_movement'         => $row->description_movement,
                    'type_movement'                => $row->type_movement,
                    'date_movement'                => $row->date_movement,
                    'amount_movement'              => $row->amount_movement,
                ];
            });
        }

    }
