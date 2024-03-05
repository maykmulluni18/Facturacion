<?php

    namespace App\Http\Resources\Tenant;

    use App\Models\Tenant\Configuration;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    /**
     * Class SaleNoteCollection
     *
     * @package App\Http\Resources\Tenant
     */
    class GeneralBoxCollection extends ResourceCollection {
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
                /** @var \App\Models\Tenant\GeneralBox $row */
                return [
                    'id'                           => $row->id,
                    'description_movement'         => $row->description_movement,
                    'category_movement'            => $row->category_movement,
                    'half_spent'                   => $row->half_spent,
                    'type_movement'                => $row->type_movement,
                    'date_movement'                => $row->date_movement,
                    'amount_movement'              => $row->amount_movement,
                ];
            });
        }

    }
