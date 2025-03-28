<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Tax;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class TaxRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function model()
    {
       return Tax::class;
    }

    public function show($id)
    {
        try {

            return $this->model->findOrFail($id)?->toJson();

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $tax =  $this->model->create([
                'name' => $request->name,
                'rate' => $request->rate,
                'status' => $request->status,
            ]);

            $locales =  Helpers::getAllActiveLocales();
            foreach ($locales as $locale) {
                $tax->setTranslation('name', $locale, $request['name'])->save();
            }

            DB::commit();
            return $tax;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $tax = $this->model->findOrFail($id);
            $tax->update($request);

            $this->setTranslation($tax, $request);

            DB::commit();
            return $tax;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            return $this->model->findOrFail($id)->destroy($id);

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function status($id, $status)
    {
        try {

            $tax = $this->model->findOrFail($id);
            $tax->update(['status' => $status]);

            return $tax;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAll($ids)
    {
        try {

            return $this->model->whereIn('id', $ids)->delete();

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function setTranslation($tax, $request)
    {
        $locale = app()->getLocale();
        return $tax->setTranslation('name', $locale, $request['name'])->save();
    }
}
