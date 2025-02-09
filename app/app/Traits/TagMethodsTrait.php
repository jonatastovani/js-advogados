<?php

namespace App\Traits;

use App\Models\Comum\IdentificacaoTags;
use App\Services\Tenant\TagTenantService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

trait TagMethodsTrait
{

    /**
     * Cria ou atualiza o relacionamento de tags associadas ao recurso.
     * 
     * @param Model $resource - O recurso ao qual as tags serão associadas.
     * @param array $tagsExistentes - As tags atualmente associadas.
     * @param array $tagsEnviadas - As tags enviadas na requisição.
     */
    public function criarAtualizarTagsEnviadas($resource, $tagsExistentes, $tagsEnviadas)
    {
        // IDs das tags existentes
        $existingTagIds = collect($tagsExistentes)->pluck('tag_id')->toArray();

        // Verifica se existem novas tags a serem criadas (se não existem no banco)
        $newTagIds = array_diff($tagsEnviadas, $existingTagIds);
        foreach ($newTagIds as $tagId) {
            IdentificacaoTags::create([
                'parent_id' => $resource->id,
                'parent_type' => $resource->getMorphClass(),
                'tag_id' => $tagId,
            ]);
        }

        // Deleta as associações que não foram enviadas
        $idsToDelete = array_diff($existingTagIds, $tagsEnviadas);
        $identificacaoDeletar = IdentificacaoTags::whereIn('tag_id', $idsToDelete)
            ->where('parent_id', $resource->id)->get();

        foreach ($identificacaoDeletar as $identificacao) {
            $identificacao->delete();
        }
    }

    protected function verificacaoTags(array $tags, Fluent $arrayErrors, array $options = []): Fluent
    {
        $tagsRetorno = [];
        foreach ($tags as $tag) {

            //Verifica se o ID da Tag informado existe
            $validacaoTagTenant = app(TagTenantService::class)->validacaoRecurso($tag, $arrayErrors);
            $arrayErrors = $validacaoTagTenant->arrayErrors;
            $tagsRetorno[] = $tag;
        }

        return new Fluent([
            'arrayErrors' => $arrayErrors,
            'tags' => $tagsRetorno,
        ]);
    }
}
