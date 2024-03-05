<?php

    namespace App\Http\Resources\Tenant;

    use App\Models\Tenant\Configuration;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    use App\Models\Tenant\Person;
    /**
     * Class SaleNoteCollection
     *
     * @package App\Http\Resources\Tenant
     */
    class SupplieCollection extends ResourceCollection {
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
                /** @var \App\Models\Tenant\Supplie $row */
                return [
                    'id'                           => $row->id,
                    'name'                         => $row->name,
                    'second_name'                  => $row->second_name,
                    'quantity'                     => $row->quantity,
                    'costs_unit'                   => $row->costs_unit,
                    'unit'                         => $row->unit,
                    'category_supplies'            => $row->category_supplies,

                ];
            });
        }

    }
