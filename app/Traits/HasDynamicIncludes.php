<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasDynamicIncludes
{
    public function loadIncludes(
        array $allowed,
        ?Request $request = null
    ): static {
        $request = $request ?? request();

        $requested = $request->has('include')
            ? explode(',', $request->query('include'))
            : [];

        $valid = array_intersect($requested, $allowed);

        if (filled($valid)) {
            $this->load($valid);
        }

        return $this;
    }
}
