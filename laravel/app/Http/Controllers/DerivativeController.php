<?php

namespace App\Http\Controllers;

use App\Filters\DerivativeFilter;
use App\Http\Requests\Filters\DerivativeFilterRequest;
use App\Repositories\AssetDerivativeRepository;
use App\Repositories\ReceivingDomainRepository;
use App\Repositories\TargetDomainRepository;
use Laracasts\Flash\Flash;
use Response;

class DerivativeController extends Controller
{
    /** @var  AssetDerivativeRepository */
    private $assetDerivativeRepository;

    /** @var TargetDomainRepository */
    protected $targetDomainRepository;

    /** @var ReceivingDomainRepository */
    protected $receivingDomainRepository;

    public function __construct(AssetDerivativeRepository $assetDerivativeRepository,
                                TargetDomainRepository $targetDomainRepository,
                                ReceivingDomainRepository $receivingDomainRepository)
    {
        $this->middleware('auth');

        $this->assetDerivativeRepository = $assetDerivativeRepository;
        $this->targetDomainRepository = $targetDomainRepository;
        $this->receivingDomainRepository = $receivingDomainRepository;
    }

    /**
     * @param DerivativeFilterRequest $request
     * @return Response
     */
    public function index(DerivativeFilterRequest $request)
    {
        $filter = app()->makeWith(DerivativeFilter::class,
            [
                'fields' => $request->all(DerivativeFilter::$fillable),
                'repository' => $this->assetDerivativeRepository
            ]
        );
        $rows = $this->assetDerivativeRepository->paginateWithFilter(
            35,
            $filter,
            ['id', 'original_id', 'item_id', 'domain', 'name1', 'price', 'old_price', 'properties', 'derivative_updated_at'],
            [],
            [
                ['id', 'desc']
            ],
            ['assetOriginal']
        );
        $targetDomains = $this->targetDomainRepository->all();
        if ($rows->isNotEmpty()) {
            $ids = $rows->pluck('domain')->unique()->filter(function ($value) { return $value !== null; })->values()->all();
            if (!empty($ids)) {
                $targetDomains = $this->targetDomainRepository->all(['id' => $ids]);
            }
        }
        $activeReceivingDomains = $this->receivingDomainRepository->all(['is_active' => true], 'sortBaseId');

        $receivingDomains = $activeReceivingDomains->filter(function($value) { return $value['bitrix_categories'] === true; });
        $activeReceivingDomainIds = $activeReceivingDomains->keys()->all();

        return view('derivative.index')
            ->with('filter', $filter)
            ->with('rows', $rows)
            ->with('targetDomains', $targetDomains ?? null)
            ->with('receivingDomains', $receivingDomains)
            ->with('activeReceivingDomains', $activeReceivingDomains)
            ->with('activeReceivingDomainIds', $activeReceivingDomainIds);
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $row = $this->assetDerivativeRepository->find($id);

        if (!isset($row)) {
            Flash::error('Производный ассет не найден.');
            return redirect(route('derivative.index'));
        }

        $targetDomain = $this->targetDomainRepository->find($row->domain);

        return view('derivative.show')
            ->with('row', $row)
            ->with('targetDomain', $targetDomain);
    }
}
