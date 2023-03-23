        select id, 'term' from terms WHERE INSTR(label, '$term') > 0 or instr(definition, '$term')
        union
        select tid, 'synonym'  from term_synonyms where instr(literal, '$term') > 0
        union
        select tid, 'existing id'  from term_existing_ids where instr(curie, '$term') > 0 or instr(iri, '$term') > 0
